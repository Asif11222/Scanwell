<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Product;
use App\Models\HealthRule;
use App\Models\Campaign;
use App\Models\MasterData;
use App\Models\Category;
use App\Models\ProductCorrection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::first();
    }

    public function test_unauthenticated_user_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('ScanWell');
        $response->assertSee('Admin Sign In');
        $response->assertSee('Email');
        $response->assertSee('Login');
        $response->assertSee('Remember me');
        $response->assertDontSee('DEMO');
        $response->assertDontSee('nadia@scanwell.app');
    }

    public function test_custom_validation_rejects_empty_credentials(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email and password need must',
            'password' => 'Email and password need must',
        ]);
    }

    public function test_custom_validation_rejects_missing_email_only(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email and password need must',
        ]);
    }

    public function test_custom_validation_rejects_missing_password_only(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@scanwell.app',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'Email and password need must',
        ]);
    }

    public function test_custom_validation_rejects_short_password(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@scanwell.app',
            'password' => '123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'Incorrect email or password',
        ]);
    }

    public function test_custom_validation_rejects_incorrect_credentials(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@scanwell.app',
            'password' => 'wrongpassword123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'Incorrect email or password',
        ]);
    }

    public function test_custom_validation_rejects_suspended_administrator(): void
    {
        // Create suspended administrator
        Admin::create([
            'name' => 'Suspended Admin',
            'email' => 'suspended@scanwell.app',
            'password' => 'password123',
            'role' => 'Support Viewer',
            'status' => 'Suspended',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'suspended@scanwell.app',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('suspended', session('errors')->first('email'));
    }

    public function test_active_admin_can_login_and_access_dashboard(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@scanwell.app',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // Access dashboard now that session is active
        $dashResponse = $this->get('/admin');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Admin');
        $dashResponse->assertSee('Catalog Products');
    }

    public function test_active_admin_can_login_with_remember_me(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@scanwell.app',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin, 'admin');
        $response->assertCookie(\Illuminate\Support\Facades\Auth::guard('admin')->getRecallerName());
    }

    public function test_dashboard_displays_zero_scans_today(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Scans Today');
        $response->assertSee('<div class="visual-kpi-value">0</div>', false);
    }

    public function test_admin_can_logout(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post('/admin/logout');
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_products_catalog_page_loads_for_authenticated_admin(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('All Products');
        $response->assertSee("Lay's American Style Cream & Onion");
    }

    public function test_product_creation_succeeds_without_auth_guard_exception(): void
    {
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Organic Almond Milk 1L',
            'brand' => 'Silk',
            'barcode' => '8901030899991',
            'category' => 'Dairy & Alternatives',
            'serving_size' => '250ml',
            'calories' => 60,
            'sugar' => 2,
            'sodium' => 110,
            'fat' => 3,
            'saturated_fat' => 0.5,
            'trans_fat' => 0,
            'status' => 'Published',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'barcode' => '8901030899991',
            'name' => 'Organic Almond Milk 1L',
            'verified' => false,
            'status' => 'Draft',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Created product',
            'user' => 'Admin',
        ]);
    }

    public function test_product_creation_automatically_evaluates_nutrition_and_assigns_health_flags(): void
    {
        // Published rule: Sodium >= 200 (Red), Trans Fat = 0 (Green)
        // Review rule: Added Sugar >= 5 (Yellow)
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'High Sodium Granola Snack',
            'brand' => 'Crispy Crunch',
            'barcode' => '8901030999999',
            'category' => 'Snacks',
            'serving_size' => '50g',
            'calories' => 220,
            'sodium' => 350, // Triggers Sodium >= 200 -> Red Flag
            'added_sugar' => 8, // Triggers Added Sugar >= 5 -> Yellow Flag
            'trans_fat' => 0, // Triggers Trans Fat = 0 -> Green Flag
            'status' => 'Published',
        ]);

        $response->assertRedirect('/admin/products');

        $product = Product::where('barcode', '8901030999999')->first();
        $this->assertNotNull($product);
        // flags_count counts total risk flags (Red + Yellow) = 2
        $this->assertEquals(2, $product->flags_count);

        // Verify JSON preview endpoint returns structured evaluation breakdown
        $previewResponse = $this->actingAs($this->admin, 'admin')->getJson("/admin/products/{$product->id}");
        $previewResponse->assertStatus(200);
        $previewResponse->assertJsonPath('evaluation.flags_count', 2);
        $previewResponse->assertJsonPath('evaluation.red_count', 1);
        $previewResponse->assertJsonPath('evaluation.yellow_count', 1);
        $previewResponse->assertJsonPath('evaluation.green_count', 1);
    }

    public function test_product_update_and_re_evaluation_works(): void
    {
        $product = Product::first();
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->put("/admin/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'brand' => $product->brand,
            'barcode' => $product->barcode,
            'category' => $product->category,
            'status' => 'Published',
            'calories' => 200,
            'sodium' => 450,
            'sugar' => 20,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    public function test_product_save_fails_when_fewer_than_3_nutrition_fields_provided(): void
    {
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Incomplete Nutrition Item',
            'brand' => 'Test Brand',
            'barcode' => '8901030777777',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 210,
            'sugar' => 12,
            // Only 2 boxes provided!
        ]);

        $response->assertSessionHasErrors(['nutrition']);
        $this->assertStringContainsString('At least 3 nutritional declaration fields must be filled', session('errors')->first('nutrition'));
    }

    public function test_product_save_succeeds_with_any_3_nutrition_fields(): void
    {
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Three Nutrient Item',
            'brand' => 'Test Brand',
            'barcode' => '8901030777778',
            'category' => 'Snacks',
            'status' => 'Published',
            'calories' => 150,
            'sugar' => 5,
            'protein' => 8,
            // Exactly 3 boxes provided; others left blank
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('barcode', '8901030777778')->first();
        $this->assertNotNull($product);
        $this->assertEquals('150 kcal', $product->nutrition['Calories']);
        $this->assertEquals('5 g', $product->nutrition['Sugar']);
        $this->assertEquals('8 g', $product->nutrition['Protein']);
        $this->assertArrayNotHasKey('Sodium', $product->nutrition);
    }

    public function test_product_update_clears_emptied_nutrition_boxes_and_reevaluates_health_flags(): void
    {
        // 1. Create a product with 6 nutrition fields (triggers Sodium >= 200 Red Flag, Trans Fat = 0 Green Flag)
        $product = Product::create([
            'name' => 'Multi Nutrient Biscuit',
            'brand' => 'Healthy Bites',
            'barcode' => '8901030777779',
            'category' => 'Snacks',
            'status' => 'Draft',
            'nutrition' => [
                'Calories' => '220 kcal',
                'Sugar' => '12 g',
                'Added Sugar' => '8 g',
                'Sodium' => '350 mg',
                'Saturated Fat' => '3 g',
                'Trans Fat' => '1 g',
            ],
            'flags_count' => 2,
        ]);

        // 2. User empties Added Sugar, Saturated Fat, and Trans Fat in the edit modal, keeping 3 boxes: Calories, Sugar, Sodium
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->putJson("/admin/products/{$product->id}", [
            'name' => $product->name,
            'brand' => $product->brand,
            'barcode' => $product->barcode,
            'category' => $product->category,
            'status' => 'Draft',
            'calories' => 180,
            'sugar' => 5, // Sugar < 10, so High Sugar yellow flag will not trigger
            'sodium' => 100, // Reduced below 200, so Sodium >= 200 red flag will NO LONGER trigger!
            'added_sugar' => '', // Emptied by user!
            'saturated_fat' => '', // Emptied by user!
            'trans_fat' => '', // Emptied by user!
            'fat' => '',
            'protein' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 3. Verify emptied boxes were removed from database
        $fresh = $product->fresh();
        $this->assertEquals('180 kcal', $fresh->nutrition['Calories']);
        $this->assertEquals('5 g', $fresh->nutrition['Sugar']);
        $this->assertEquals('100 mg', $fresh->nutrition['Sodium']);
        $this->assertArrayNotHasKey('Added Sugar', $fresh->nutrition);
        $this->assertArrayNotHasKey('Saturated Fat', $fresh->nutrition);
        $this->assertArrayNotHasKey('Trans Fat', $fresh->nutrition);
        $this->assertArrayNotHasKey('added_sugar', $fresh->nutrition);

        // Sodium is 100 (< 200 threshold), Sugar is 5 (< 10 threshold), and Added Sugar is removed, so risk flags_count is 0
        $this->assertEquals(0, $fresh->flags_count);
    }

    public function test_product_details_visual_explorer_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/product-details');
        $response->assertStatus(200);
        $response->assertSee('Product Details');
        $response->assertSee('Visual catalog explorer');
        $response->assertSee('View Details');
        $response->assertSee('Edit');
        $response->assertSee('previewProduct(');
        $response->assertSee('editProduct(');
    }

    public function test_verification_queue_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/verification-queue');
        $response->assertStatus(200);
        $response->assertSee('Verification Queue');
        $response->assertSee('previewProduct(');
        $response->assertSee('editProduct(');
        $response->assertSee('Verify &amp; Publish', false);
    }

    public function test_new_product_is_unverified_draft_and_enters_verification_queue(): void
    {
        $response = $this->from('/admin/products')->actingAs($this->admin, 'admin')->post('/admin/products', [
            'name' => 'Unverified Draft Snack',
            'brand' => 'Nature Bites',
            'barcode' => '8901030666666',
            'category' => 'Snacks',
            'calories' => 120,
            'sugar' => 4,
            'sodium' => 90,
            'status' => 'Published', // Even if submitted as Published, store() forces unverified Draft
        ]);

        $response->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'barcode' => '8901030666666',
            'verified' => false,
            'status' => 'Draft',
        ]);

        // It must automatically appear in the Verification Queue
        $queueResponse = $this->actingAs($this->admin, 'admin')->get('/admin/verification-queue');
        $queueResponse->assertStatus(200);
        $queueResponse->assertSee('8901030666666');
        $queueResponse->assertSee('Unverified Draft Snack');
    }

    public function test_verifying_product_sets_verified_true_and_status_published(): void
    {
        // 1. Create unverified product
        $product = Product::create([
            'name' => 'Raw Sourced Juice',
            'brand' => 'Pure Juice Co',
            'barcode' => '8901030555555',
            'category' => 'Beverages',
            'status' => 'Draft',
            'verified' => false,
            'nutrition' => ['Calories' => '90 kcal', 'Sugar' => '15 g', 'Sodium' => '20 mg'],
            'flags_count' => 0,
        ]);

        // Verify it is visible in the verification queue initially
        $queueBefore = $this->actingAs($this->admin, 'admin')->get('/admin/verification-queue');
        $queueBefore->assertSee('8901030555555');

        // 2. Perform verify action
        $verifyResponse = $this->actingAs($this->admin, 'admin')->post("/admin/products/{$product->id}/verify");
        $verifyResponse->assertRedirect();

        // 3. Database check: verified is true AND status is Published
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'verified' => true,
            'status' => 'Published',
        ]);

        // 4. Product automatically departed verification queue
        $queueAfter = $this->actingAs($this->admin, 'admin')->get('/admin/verification-queue');
        $queueAfter->assertDontSee('8901030555555');

        // 5. Product is now listed as Verified in products catalog
        $catalogResponse = $this->actingAs($this->admin, 'admin')->get('/admin/products');
        $catalogResponse->assertSee('Raw Sourced Juice');
        $catalogResponse->assertSee('8901030555555');
    }

    public function test_updating_product_with_verified_checkbox_auto_publishes_product(): void
    {
        $product = Product::create([
            'name' => 'Pending Verification Granola',
            'brand' => 'Granola Lab',
            'barcode' => '8901030444444',
            'category' => 'Snacks',
            'status' => 'Draft',
            'verified' => false,
            'nutrition' => ['Calories' => '180 kcal', 'Sugar' => '6 g', 'Protein' => '5 g'],
            'flags_count' => 0,
        ]);

        // Admin updates product and checks verified = 1 (while leaving or passing status = Draft)
        $response = $this->actingAs($this->admin, 'admin')->putJson("/admin/products/{$product->id}", [
            'name' => $product->name,
            'brand' => $product->brand,
            'barcode' => $product->barcode,
            'category' => $product->category,
            'status' => 'Draft',
            'verified' => 1,
            'calories' => 180,
            'sugar' => 6,
            'protein' => 5,
        ]);

        $response->assertStatus(200);
        $fresh = $product->fresh();
        $this->assertTrue((bool)$fresh->verified);
        $this->assertEquals('Published', $fresh->status);
    }

    public function test_ocr_submissions_queue_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/submissions');
        $response->assertStatus(200);
        $response->assertSee('OCR / Product Review');
        $response->assertSee('SW-10482');
    }

    public function test_product_corrections_pages_load(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/product-corrections');
        $response->assertStatus(200);
        $response->assertSee('Product Corrections');

        $responsePending = $this->actingAs($this->admin, 'admin')->get('/admin/pending-corrections');
        $responsePending->assertStatus(200);
        $responsePending->assertSee('Pending Corrections');
    }

    public function test_duplicate_products_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/duplicate-products');
        $response->assertStatus(200);
        $response->assertSee('Duplicate Products');
        $response->assertSee('DP-311');
    }

    public function test_resolving_duplicate_removes_resolve_button(): void
    {
        $duplicate = \App\Models\ProductDuplicate::where('id', 'DP-299')->first();
        $this->assertNotNull($duplicate);
        $this->assertEquals('Review', $duplicate->status);

        // Before resolving, the Resolve button trigger is rendered
        $pageBefore = $this->actingAs($this->admin, 'admin')->get('/admin/duplicate-products');
        $pageBefore->assertStatus(200);
        $pageBefore->assertSee("openDuplicateModal('DP-299", false);

        // Resolve the duplicate
        $resolveResponse = $this->actingAs($this->admin, 'admin')->post("/admin/duplicate-products/{$duplicate->id}/resolve", [
            'action' => 'Resolved',
        ]);
        $resolveResponse->assertRedirect();
        $this->assertEquals('Resolved', $duplicate->fresh()->status);

        // Verify that on DP-299, the Resolve button is gone
        $pageAfter = $this->actingAs($this->admin, 'admin')->get('/admin/duplicate-products');
        $pageAfter->assertStatus(200);
        $pageAfter->assertDontSee("onclick=\"openDuplicateModal('DP-299", false);

        // When all duplicates are resolved, no resolve buttons exist on the page
        \App\Models\ProductDuplicate::query()->update(['status' => 'Resolved']);
        $pageAllResolved = $this->actingAs($this->admin, 'admin')->get('/admin/duplicate-products');
        $pageAllResolved->assertStatus(200);
        $pageAllResolved->assertDontSee("onclick=\"openDuplicateModal(", false);
    }

    public function test_health_rules_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/health-intelligence');
        $response->assertStatus(200);
        $response->assertSee('Health Intelligence');
        $response->assertSee('High sodium — general packaged food');
    }

    public function test_health_concerns_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/health-concerns');
        $response->assertStatus(200);
        $response->assertSee('Health Concerns');
        $response->assertSee('Diabetes');
        $response->assertSee('High Blood Pressure');
    }

    public function test_personalized_alerts_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/personalized-alerts');
        $response->assertStatus(200);
        $response->assertSee('Personalized In-App Alerts');
        $response->assertSee('Live Mobile App Emulator');
    }

    public function test_ads_and_promotions_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/ads');
        $response->assertStatus(200);
        $response->assertSee('Ads & Promotions');
        $response->assertSee('Healthy Breakfast Week');
    }

    public function test_app_content_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/content');
        $response->assertStatus(200);
        $response->assertSee('App Content');
        $response->assertSee('onboarding.health_flags.title');
    }

    public function test_notifications_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/notifications');
        $response->assertStatus(200);
        $response->assertSee('Push Notifications');
        $response->assertSee('Your saved product was updated');
    }

    public function test_users_and_contributors_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Users & Contributors');
        $response->assertSee('Ammu Rahman');
        $response->assertSee('title="Delete User"', false);
    }

    public function test_super_admin_can_delete_user(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Temporary Test User',
            'email' => 'temptestuser@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Consumer',
            'status' => 'Active',
            'scans_count' => 10,
            'submissions_count' => 2,
            'verified' => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->delete('/admin/users/' . $user->id);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted user temptestuser@example.com',
            'status' => 'Deleted',
        ]);
    }

    public function test_non_super_admin_cannot_delete_user(): void
    {
        $productManager = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_PRODUCT_MANAGER)->first();
        $user = \App\Models\User::first();

        $response = $this->actingAs($productManager, 'admin')->delete('/admin/users/' . $user->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_master_data_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/master-data');
        $response->assertStatus(200);
        $response->assertSee('Master Data');
        $response->assertSee('masterDataNavGroup');
        $response->assertSee('submenu-tree');
        $response->assertSee('Categories');
        $response->assertSee('Nutrients');
        $response->assertSee('Health Concerns');

        // Verify active state when tab is specified
        $tabResponse = $this->actingAs($this->admin, 'admin')->get('/admin/master-data?tab=Nutrients');
        $tabResponse->assertStatus(200);
        $tabResponse->assertSee('Master Data: Nutrients');
        $tabResponse->assertSee('tab=Nutrients" class="submenu-link active"', false);
    }

    public function test_app_control_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/app-control');
        $response->assertStatus(200);
        $response->assertSee('App Control');
        $response->assertSee('Maintenance Mode');
        $response->assertSee('Registration Enabled');
    }

    public function test_security_and_access_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/security');
        $response->assertStatus(200);
        $response->assertSee('Admin & Security');
        $response->assertSee('Admin');
    }

    public function test_global_search_endpoint_returns_json_results(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->getJson('/admin/search?q=lays');
        $response->assertStatus(200);
        $response->assertJsonStructure(['groups']);
    }

    public function test_custom_validation_rejects_invalid_barcode_on_product_create(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Test Product',
            'brand' => 'Test Brand',
            'barcode' => '123', // Invalid short dummy barcode
            'category' => 'Snacks',
            'status' => 'Draft',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['barcode']);
    }

    public function test_custom_validation_rejects_negative_threshold_on_health_rule(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/health-intelligence', [
            'name' => 'Negative Sodium Rule',
            'target' => 'Sodium',
            'operator' => '>=',
            'threshold' => '-50', // Negative threshold invalid
            'severity' => 'Red / High Concern',
            'status' => 'Draft',
            'priority' => 50,
            'message' => 'Test alert advisory message for sodium.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['threshold']);
    }

    public function test_custom_validation_rejects_deceptive_medical_claims_in_ads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/ads', [
            'name' => 'Miracle Breakfast Bar',
            'type' => 'Card',
            'headline' => 'Guaranteed weight loss in 3 days', // Violates AdSafetyCheckRule
            'copy' => 'This bar cures diabetes and overrides health flags completely.', // Violates AdSafetyCheckRule
            'cta' => 'Buy Now',
            'cta_url' => 'scanwell://search',
            'placement' => 'Home Feed',
            'start_at' => now()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'priority' => 50,
            'frequency_cap' => 1,
            'status' => 'Draft',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['headline', 'copy']);
    }

    public function test_adding_category_in_master_data_syncs_and_appears_in_product_modal(): void
    {
        // 1. Post new category to master data
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Categories',
            'name' => 'Organic Smoothies',
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // 2. Verify database records
        $this->assertDatabaseHas('master_data', [
            'type' => 'Categories',
            'name' => 'Organic Smoothies',
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Organic Smoothies',
        ]);

        // 3. Verify it appears in All Products page category dropdown
        $productsPage = $this->actingAs($this->admin, 'admin')->get('/admin/products');
        $productsPage->assertStatus(200);
        $productsPage->assertSee('Organic Smoothies');

        // 4. Verify it appears in Dashboard quick Add Product category dropdown
        $dashboardPage = $this->actingAs($this->admin, 'admin')->get('/admin');
        $dashboardPage->assertStatus(200);
        $dashboardPage->assertSee('Organic Smoothies');
    }

    public function test_master_data_custom_validation_rejects_duplicate_category(): void
    {
        // First add a category
        $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Categories',
            'name' => 'Kombucha & Ferments',
            'is_active' => true,
        ]);

        // Attempt duplicate
        $dupResponse = $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Categories',
            'name' => 'Kombucha & Ferments',
            'is_active' => true,
        ]);

        $dupResponse->assertStatus(422);
        $dupResponse->assertJsonValidationErrors(['name']);
        $this->assertEquals(
            'This entry already exists within the selected master data taxonomy.',
            $dupResponse->json('errors.name.0')
        );
    }

    public function test_admin_can_update_master_data_and_synced_category(): void
    {
        // 1. Create a category via master data
        $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Categories',
            'name' => 'Cold Pressed Juices',
            'is_active' => true,
        ]);

        $item = MasterData::where('name', 'Cold Pressed Juices')->first();
        $this->assertNotNull($item);

        // 2. Update the master data entry
        $updateResp = $this->actingAs($this->admin, 'admin')->putJson("/admin/master-data/{$item->id}", [
            'type' => 'Categories',
            'name' => 'Artisanal Juices',
            'is_active' => false,
        ]);

        $updateResp->assertStatus(200);
        $updateResp->assertJson(['success' => true]);

        // 3. Verify MasterData updated
        $this->assertDatabaseHas('master_data', [
            'id' => $item->id,
            'name' => 'Artisanal Juices',
            'is_active' => false,
        ]);

        // 4. Verify Category updated
        $this->assertDatabaseHas('categories', [
            'name' => 'Artisanal Juices',
            'is_active' => false,
        ]);
        $this->assertDatabaseMissing('categories', [
            'name' => 'Cold Pressed Juices',
        ]);

        // 5. Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Updated master data value',
        ]);
    }

    public function test_updating_master_data_with_same_name_passes_validation(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Nutrients',
            'name' => 'Dietary Fiber',
            'is_active' => true,
        ]);

        $item = MasterData::where('name', 'Dietary Fiber')->first();
        $this->assertNotNull($item);

        // Update keeping same name, changing is_active
        $updateResp = $this->actingAs($this->admin, 'admin')->putJson("/admin/master-data/{$item->id}", [
            'type' => 'Nutrients',
            'name' => 'Dietary Fiber',
            'is_active' => false,
        ]);

        $updateResp->assertStatus(200);
        $this->assertDatabaseHas('master_data', [
            'id' => $item->id,
            'name' => 'Dietary Fiber',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_master_data_and_synced_category(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson('/admin/master-data', [
            'type' => 'Categories',
            'name' => 'Sparkling Teas',
            'is_active' => true,
        ]);

        $item = MasterData::where('name', 'Sparkling Teas')->first();
        $this->assertNotNull($item);
        $this->assertDatabaseHas('categories', ['name' => 'Sparkling Teas']);

        // Delete the master data entry
        $deleteResp = $this->actingAs($this->admin, 'admin')->deleteJson("/admin/master-data/{$item->id}");
        $deleteResp->assertStatus(200);
        $deleteResp->assertJson(['success' => true]);

        // Verify records removed
        $this->assertDatabaseMissing('master_data', ['id' => $item->id]);
        $this->assertDatabaseMissing('categories', ['name' => 'Sparkling Teas']);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted master data value',
        ]);
    }

    public function test_master_data_page_renders_actions_column_and_buttons(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/master-data?tab=Categories');
        $response->assertStatus(200);
        $response->assertSee('Actions');
        $response->assertSee('openAddMasterModal');
        $response->assertSee('openEditMasterModal');
        $response->assertSee('openDeleteMasterModal');
    }

    public function test_admin_can_create_product_with_uploaded_picture(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('healthy_granola.jpg', 600, 600);

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Organic Honey Granola',
            'brand' => 'Nature Valley',
            'barcode' => '8901999888777',
            'category' => 'Breakfast Cereals',
            'status' => 'Draft',
            'sodium' => 150,
            'sugar' => 8,
            'fat' => 5,
            'image' => $image,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product = Product::where('barcode', '8901999888777')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_url);
        $this->assertTrue(Str::startsWith($product->image_url, '/storage/products/'));

        // Verify image exists on public storage disk
        $storagePath = Str::replaceFirst('/storage/', '', $product->image_url);
        Storage::disk('public')->assertExists($storagePath);

        // Verify product index table displays the uploaded image
        $indexResponse = $this->actingAs($this->admin, 'admin')->get('/admin/products');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($product->image_url);
        $indexResponse->assertSee('Organic Honey Granola');
    }

    public function test_admin_can_update_and_remove_product_picture(): void
    {
        Storage::fake('public');

        $initialImage = UploadedFile::fake()->image('initial.jpg', 400, 400);

        $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Crispy Protein Bites',
            'brand' => 'FitBite',
            'barcode' => '8901111222333',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 120,
            'sugar' => 3,
            'fat' => 4,
            'image' => $initialImage,
        ]);

        $product = Product::where('barcode', '8901111222333')->first();
        $oldImagePath = Str::replaceFirst('/storage/', '', $product->image_url);
        Storage::disk('public')->assertExists($oldImagePath);

        // Update with new image
        $newImage = UploadedFile::fake()->image('updated.png', 400, 400);
        $updateResponse = $this->actingAs($this->admin, 'admin')->putJson("/admin/products/{$product->id}", [
            'name' => 'Crispy Protein Bites',
            'brand' => 'FitBite',
            'barcode' => '8901111222333',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 120,
            'sugar' => 3,
            'fat' => 4,
            'image' => $newImage,
        ]);

        $updateResponse->assertStatus(200);
        $product->refresh();
        $newImagePath = Str::replaceFirst('/storage/', '', $product->image_url);
        Storage::disk('public')->assertExists($newImagePath);
        Storage::disk('public')->assertMissing($oldImagePath);

        // Remove image
        $removeResponse = $this->actingAs($this->admin, 'admin')->putJson("/admin/products/{$product->id}", [
            'name' => 'Crispy Protein Bites',
            'brand' => 'FitBite',
            'barcode' => '8901111222333',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 120,
            'sugar' => 3,
            'fat' => 4,
            'remove_image' => '1',
        ]);

        $removeResponse->assertStatus(200);
        $product->refresh();
        $this->assertNull($product->image_url);
    }

    public function test_product_picture_validation_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Invalid Image Product',
            'brand' => 'Test Brand',
            'barcode' => '8907777666555',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 100,
            'sugar' => 5,
            'fat' => 2,
            'image' => $fakePdf,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    public function test_product_name_is_required_with_custom_message(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => '',
            'brand' => 'Test Brand',
            'barcode' => '8901234567999',
            'category' => 'Snacks',
            'status' => 'Draft',
            'sodium' => 100,
            'sugar' => 5,
            'fat' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        $this->assertEquals(
            'Product name must!',
            $response->json('errors.name.0')
        );
    }

    public function test_check_name_endpoint_detects_existing_and_unique_names(): void
    {
        // 1. Existing product name
        $resExists = $this->actingAs($this->admin, 'admin')->getJson('/admin/products/check-name?name=' . urlencode('Froot Loops'));
        $resExists->assertStatus(200);
        $resExists->assertJson(['exists' => true]);

        // 2. Case-insensitive existing name
        $resLower = $this->actingAs($this->admin, 'admin')->getJson('/admin/products/check-name?name=' . urlencode('froot loops'));
        $resLower->assertStatus(200);
        $resLower->assertJson(['exists' => true]);

        // 3. New unique name
        $resUnique = $this->actingAs($this->admin, 'admin')->getJson('/admin/products/check-name?name=' . urlencode('Super Unique Berry Crunch 999'));
        $resUnique->assertStatus(200);
        $resUnique->assertJson(['exists' => false]);

        // 4. Empty name
        $resEmpty = $this->actingAs($this->admin, 'admin')->getJson('/admin/products/check-name?name=');
        $resEmpty->assertStatus(200);
        $resEmpty->assertJson(['exists' => false, 'empty' => true, 'message' => 'Product name must!']);
    }

    public function test_product_name_must_be_unique_and_rejects_duplicates(): void
    {
        // Exact duplicate name
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Froot Loops',
            'brand' => 'Another Brand',
            'barcode' => '8909876543210',
            'category' => 'Breakfast Cereals',
            'status' => 'Draft',
            'sodium' => 100,
            'sugar' => 5,
            'fat' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        $this->assertEquals(
            'This product name already exists in the catalog.',
            $response->json('errors.name.0')
        );

        // Case-insensitive duplicate name
        $caseResponse = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'froot loops',
            'brand' => 'Another Brand',
            'barcode' => '8909876543211',
            'category' => 'Breakfast Cereals',
            'status' => 'Draft',
            'sodium' => 100,
            'sugar' => 5,
            'fat' => 2,
        ]);

        $caseResponse->assertStatus(422);
        $caseResponse->assertJsonValidationErrors(['name']);
        $this->assertEquals(
            'This product name already exists in the catalog.',
            $caseResponse->json('errors.name.0')
        );
    }

    public function test_admin_can_delete_a_product(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('temp_snack.jpg', 300, 300);
        $imagePath = $image->store('products', 'public');

        $product = Product::create([
            'name' => 'Temporary Deletable Snack',
            'brand' => 'TestBrand',
            'barcode' => '8909990001112',
            'category' => 'Snacks',
            'status' => 'Draft',
            'verified' => false,
            'flags_count' => 0,
            'image_url' => '/storage/' . $imagePath,
            'nutrition' => ['Calories' => '100 kcal', 'Sugar' => '2 g', 'Sodium' => '50 mg'],
        ]);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($this->admin, 'admin')->delete("/admin/products/{$product->id}");
        $response->assertStatus(302);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted product',
            'status' => 'Deleted',
        ]);
    }

    public function test_delete_button_renders_in_product_table_actions(): void
    {
        $product = Product::first();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee(route('admin.products.destroy', $product));
        $response->assertSee('title="Delete Product"', false);
    }

    public function test_admin_can_create_new_manager_or_admin(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/security/admins', [
            'name' => 'Tariq Al-Mansoor',
            'email' => 'tariq@scanwell.app',
            'password' => 'Secret123!',
            'role' => 'Product Manager',
            'status' => 'Active',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('admins', [
            'name' => 'Tariq Al-Mansoor',
            'email' => 'tariq@scanwell.app',
            'role' => 'Product Manager',
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Created admin staff',
        ]);
    }

    public function test_create_admin_validation_errors_returned_for_invalid_credentials(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/security/admins', [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'role', 'status']);
        $response->assertJsonPath('errors.name.0', 'Staff full name is required.');
        $response->assertJsonPath('errors.email.0', 'A valid corporate email address is required.');
        $response->assertJsonPath('errors.password.0', 'A secure initial password (at least 6 characters) is required.');
    }

    public function test_admin_can_update_admin_staff_details_and_role(): void
    {
        $staff = Admin::create([
            'name' => 'Jannat Ara',
            'email' => 'jannat@scanwell.app',
            'password' => \Illuminate\Support\Facades\Hash::make('OldPass123!'),
            'role' => 'Support Viewer',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->putJson("/admin/security/admins/{$staff->id}", [
            'name' => 'Jannat Ara Promoted',
            'email' => 'jannat@scanwell.app',
            'role' => 'Marketing Manager',
            'status' => 'Active',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('admins', [
            'id' => $staff->id,
            'name' => 'Jannat Ara Promoted',
            'role' => 'Marketing Manager',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Updated admin staff',
        ]);
    }

    public function test_admin_can_remove_admin_staff(): void
    {
        $staff = Admin::create([
            'name' => 'Temporary Reviewer',
            'email' => 'temp.reviewer@scanwell.app',
            'password' => \Illuminate\Support\Facades\Hash::make('Pass123!'),
            'role' => 'Submission Reviewer',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->deleteJson("/admin/security/admins/{$staff->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('admins', ['id' => $staff->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted admin staff',
        ]);
    }

    public function test_admin_cannot_delete_own_active_account(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->deleteJson("/admin/security/admins/{$this->admin->id}");
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $this->assertDatabaseHas('admins', ['id' => $this->admin->id]);
    }

    public function test_security_admin_users_view_renders_add_and_actions(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/security?tab=admins');
        $response->assertStatus(200);
        $response->assertSee('Add Admin / Manager');
        $response->assertSee('Actions');
        $response->assertSee('openAddAdminModal');
        $response->assertSee('openEditAdminModal');
        $response->assertSee('openDeleteAdminModal');
        $response->assertSee('submitAdminForm');
        $response->assertSee('modal-form-errors-container');
        $response->assertSee('You');
    }

    public function test_admin_can_delete_health_rule(): void
    {
        $rule = HealthRule::create([
            'name' => 'Custom Test Rule For Deletion',
            'target' => 'Sodium',
            'operator' => '>=',
            'threshold' => '300',
            'unit' => 'mg',
            'severity' => 'Red / High Concern',
            'health_concern' => 'High Blood Pressure',
            'status' => 'Draft',
            'priority' => 40,
            'message' => 'High sodium detected',
            'version' => '1.0',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->deleteJson("/admin/health-intelligence/{$rule->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('health_rules', ['id' => $rule->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted health rule',
        ]);
    }

    public function test_health_rules_view_renders_delete_button_in_action_column(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/health-intelligence');
        $response->assertStatus(200);
        $response->assertSee('openDeleteRuleModal');
        $response->assertSee('Delete Rule');
        $response->assertSee('submitRuleForm');
        $response->assertSee('modal-form-errors-container');
    }

    public function test_health_rule_create_fails_with_validation_errors_for_invalid_credentials(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/health-intelligence', [
            'name' => 'Ab',
            'target' => 'Sodium',
            'operator' => '>=',
            'severity' => 'Red / High Concern',
            'status' => 'Draft',
            'priority' => 50,
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'message']);
        $response->assertJsonPath('errors.name.0', 'The health rule name field must be at least 4 characters.');
        $response->assertJsonPath('errors.message.0', 'The alert message shown to consumers when this rule triggers cannot be blank.');
    }

    public function test_product_corrections_view_renders_action_button(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/product-corrections');
        $response->assertStatus(200);
        $response->assertSee('Action');
        $response->assertSee('openCorrectionReview');
        $response->assertDontSee('title="Review Correction"');
    }

    public function test_product_corrections_action_button_neutralized_when_not_review(): void
    {
        // Ensure we have one in Review, one Approved, one Rejected
        $review = ProductCorrection::where('status', 'Review')->first();
        $approved = ProductCorrection::where('status', 'Approved')->first();
        $rejected = ProductCorrection::where('status', 'Rejected')->first();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/product-corrections');
        $response->assertStatus(200);

        if ($review) {
            $response->assertSee("openCorrectionReview('{$review->id}')", false);
        }

        if ($approved) {
            $response->assertDontSee("openCorrectionReview('{$approved->id}')", false);
            $response->assertSee('Action neutralized (Approved)', false);
        }

        if ($rejected) {
            $response->assertDontSee("openCorrectionReview('{$rejected->id}')", false);
            $response->assertSee('Action neutralized (Rejected)', false);
        }
    }

    public function test_admin_can_delete_product_correction(): void
    {
        $correction = ProductCorrection::first();

        $response = $this->actingAs($this->admin, 'admin')->deleteJson("/admin/product-corrections/{$correction->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('product_corrections', ['id' => $correction->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted product correction',
        ]);
    }

    public function test_admin_can_add_product_with_dynamic_nutrients(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Dynamic Nutrients Snack Bar',
            'brand' => 'Nature Fuel',
            'barcode' => '8901239998877',
            'category' => 'Snacks',
            'status' => 'Draft',
            'nutrient_names' => ['Sodium (mg)', 'Calcium (mg)', 'Fiber (g)'],
            'nutrient_values' => [120, 250, 6],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product = Product::where('barcode', '8901239998877')->first();
        $this->assertNotNull($product);
        $this->assertEquals('120 mg', $product->nutrition['Sodium']);
        $this->assertEquals('250 mg', $product->nutrition['Calcium']);
        $this->assertEquals('6 g', $product->nutrition['Fiber']);
    }

    public function test_admin_can_update_product_by_removing_and_renaming_nutrients(): void
    {
        $product = Product::create([
            'name' => 'Editable Nutrients Drink',
            'brand' => 'JuiceCo',
            'barcode' => '8909876543210',
            'category' => 'Beverages',
            'status' => 'Published',
            'nutrition' => [
                'Sodium' => '80 mg',
                'Sugar' => '25 g',
                'Trans Fat' => '0 g',
            ],
            'verified' => true,
        ]);

        // Update removing Trans Fat and renaming Sugar to Total Sugars (g)
        $response = $this->actingAs($this->admin, 'admin')->putJson("/admin/products/{$product->id}", [
            'name' => 'Editable Nutrients Drink',
            'brand' => 'JuiceCo',
            'barcode' => '8909876543210',
            'category' => 'Beverages',
            'status' => 'Published',
            'nutrient_names' => ['Sodium (mg)', 'Total Sugars (g)', 'Vitamin C (mg)'],
            'nutrient_values' => [80, 22, 60],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals('80 mg', $product->nutrition['Sodium']);
        $this->assertEquals('22 g', $product->nutrition['Total Sugars']);
        $this->assertEquals('60 mg', $product->nutrition['Vitamin C']);
        $this->assertArrayNotHasKey('Trans Fat', $product->nutrition);
    }

    public function test_dynamic_nutrients_validation_fails_with_fewer_than_3(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Insufficient Nutrients Drink',
            'brand' => 'JuiceCo',
            'barcode' => '8909876543219',
            'category' => 'Beverages',
            'status' => 'Draft',
            'nutrient_names' => ['Sodium (mg)', 'Sugar (g)'],
            'nutrient_values' => [80, 22],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nutrition']);
    }

    public function test_dynamic_nutrients_duplicate_name_fails_validation(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson('/admin/products', [
            'name' => 'Duplicate Nutrients Drink',
            'brand' => 'JuiceCo',
            'barcode' => '8909876543221',
            'category' => 'Beverages',
            'status' => 'Draft',
            'nutrient_names' => ['Sugar (g)', 'Sugar', 'Protein (g)'],
            'nutrient_values' => [10, 10, 5],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nutrient_names']);
        $response->assertJsonFragment([
            'nutrient_names' => ['The nutrient already exists'],
        ]);
    }

    public function test_super_admin_has_full_clearance_including_security_and_app_control(): void
    {
        $superAdmin = Admin::where('role', 'Super Admin')->first();
        $this->assertNotNull($superAdmin);

        $this->actingAs($superAdmin, 'admin')
            ->get('/admin/security')
            ->assertStatus(200);

        $this->actingAs($superAdmin, 'admin')
            ->get('/admin/app-control')
            ->assertStatus(200);
    }

    public function test_product_manager_can_manage_products_but_forbidden_from_security(): void
    {
        $productMgr = Admin::where('role', 'Product Manager')->first();
        $this->assertNotNull($productMgr);

        // Product Manager can view and create products
        $this->actingAs($productMgr, 'admin')
            ->get('/admin/products')
            ->assertStatus(200);

        // Product Manager is FORBIDDEN from Security / Staff management
        $this->actingAs($productMgr, 'admin')
            ->get('/admin/security')
            ->assertStatus(403);

        // Product Manager is FORBIDDEN from App Control
        $this->actingAs($productMgr, 'admin')
            ->get('/admin/app-control')
            ->assertStatus(403);
    }

    public function test_health_reviewer_can_manage_health_rules_but_forbidden_from_product_creation(): void
    {
        $healthReviewer = Admin::where('role', 'Health Content Reviewer')->first();
        $this->assertNotNull($healthReviewer);

        // Health Reviewer can access health rules
        $this->actingAs($healthReviewer, 'admin')
            ->get('/admin/health-intelligence')
            ->assertStatus(200);

        // Health Reviewer is FORBIDDEN from creating products
        $this->actingAs($healthReviewer, 'admin')
            ->postJson('/admin/products', [
                'name' => 'Forbidden Product By Health Reviewer',
                'brand' => 'Test',
                'barcode' => '9991112223334',
                'category' => 'Snacks',
            ])
            ->assertStatus(403);

        // Health Reviewer is FORBIDDEN from Security
        $this->actingAs($healthReviewer, 'admin')
            ->get('/admin/security')
            ->assertStatus(403);
    }

    public function test_submission_reviewer_can_review_submissions_but_forbidden_from_security(): void
    {
        $submissionReviewer = Admin::where('role', 'Submission Reviewer')->first();
        $this->assertNotNull($submissionReviewer);

        // Submission Reviewer can access submissions
        $this->actingAs($submissionReviewer, 'admin')
            ->get('/admin/submissions')
            ->assertStatus(200);

        // Submission Reviewer can access corrections
        $this->actingAs($submissionReviewer, 'admin')
            ->get('/admin/product-corrections')
            ->assertStatus(200);

        // Submission Reviewer is FORBIDDEN from Security
        $this->actingAs($submissionReviewer, 'admin')
            ->get('/admin/security')
            ->assertStatus(403);
    }

    public function test_marketing_manager_can_manage_campaigns_but_forbidden_from_security(): void
    {
        $marketingMgr = Admin::where('role', 'Marketing Manager')->first();
        $this->assertNotNull($marketingMgr);

        // Marketing Manager can access campaigns
        $this->actingAs($marketingMgr, 'admin')
            ->get('/admin/ads')
            ->assertStatus(200);

        // Marketing Manager is FORBIDDEN from creating products
        $this->actingAs($marketingMgr, 'admin')
            ->postJson('/admin/products', [
                'name' => 'Forbidden Product By Marketing',
                'brand' => 'Test',
                'barcode' => '9991112223335',
                'category' => 'Snacks',
            ])
            ->assertStatus(403);

        // Marketing Manager is FORBIDDEN from Security
        $this->actingAs($marketingMgr, 'admin')
            ->get('/admin/security')
            ->assertStatus(403);
    }

    public function test_support_viewer_is_strictly_read_only_and_forbidden_from_mutations(): void
    {
        $supportViewer = Admin::where('role', 'Support Viewer')->first();
        $this->assertNotNull($supportViewer);

        // Support Viewer can view products (Read-Only)
        $this->actingAs($supportViewer, 'admin')
            ->get('/admin/products')
            ->assertStatus(200);

        // Support Viewer is FORBIDDEN from creating products (POST)
        $this->actingAs($supportViewer, 'admin')
            ->postJson('/admin/products', [
                'name' => 'Forbidden Product By Support',
                'brand' => 'Test',
                'barcode' => '9991112223336',
                'category' => 'Snacks',
            ])
            ->assertStatus(403);

        // Support Viewer is FORBIDDEN from Security
        $this->actingAs($supportViewer, 'admin')
            ->get('/admin/security')
            ->assertStatus(403);

        // Support Viewer is FORBIDDEN from creating Health Rules
        $this->actingAs($supportViewer, 'admin')
            ->postJson('/admin/health-intelligence', [
                'name' => 'Test Rule',
                'target' => 'Sugar',
            ])
            ->assertStatus(403);
    }

    public function test_submission_review_action_flash_messages_and_styles(): void
    {
        $submission = \App\Models\ProductSubmission::first();
        $this->assertNotNull($submission);

        // 1. Reject action
        $resReject = $this->actingAs($this->admin, 'admin')
            ->from('/admin/submissions')
            ->post("/admin/submissions/{$submission->id}/review", [
                'status' => 'Rejected',
            ]);
        $resReject->assertRedirect('/admin/submissions');
        $resReject->assertSessionHas('status_type', 'rejected');
        $resReject->assertSessionHas('status_title', 'Rejected');
        $resReject->assertSessionHas('status_message', "Submission {$submission->id} is Rejected");

        $followReject = $this->actingAs($this->admin, 'admin')->get('/admin/submissions');
        $followReject->assertSee('Rejected');
        $followReject->assertSee("Submission {$submission->id} is Rejected");
        $followReject->assertSee('color:#dc2626', false);

        // 2. Request Correction (status = Review)
        $resReview = $this->actingAs($this->admin, 'admin')
            ->from('/admin/submissions')
            ->post("/admin/submissions/{$submission->id}/review", [
                'status' => 'Review',
            ]);
        $resReview->assertRedirect('/admin/submissions');
        $resReview->assertSessionHas('status_type', 'review');
        $resReview->assertSessionHas('status_title', 'Under Review');
        $resReview->assertSessionHas('status_message', "Submission {$submission->id} is Under Review");

        $followReview = $this->actingAs($this->admin, 'admin')->get('/admin/submissions');
        $followReview->assertSee('Under Review');
        $followReview->assertSee("Submission {$submission->id} is Under Review");
        $followReview->assertSee('color:#b45309', false);

        // 3. Approve to Catalog (status = Approved)
        $resApprove = $this->actingAs($this->admin, 'admin')
            ->from('/admin/submissions')
            ->post("/admin/submissions/{$submission->id}/review", [
                'status' => 'Approved',
            ]);
        $resApprove->assertRedirect('/admin/submissions');
        $resApprove->assertSessionHas('status_type', 'success');
        $resApprove->assertSessionHas('status_title', 'Success');
        $resApprove->assertSessionHas('status_message', "Submission {$submission->id} processed as Approved.");

        $followApprove = $this->actingAs($this->admin, 'admin')->get('/admin/submissions');
        $followApprove->assertSee('Success');
        $followApprove->assertSee("Submission {$submission->id} processed as Approved.");
    }

    public function test_super_admin_can_view_permission_matrix_tab(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/security?tab=matrix');

        $response->assertStatus(200);
        $response->assertSee('Permission Matrix');
        $response->assertSee('MODULE');
        $response->assertSee('VIEW');
        $response->assertSee('CREATE');
        $response->assertSee('EDIT OWN');
        $response->assertSee('EDIT ALL');
        $response->assertSee('DELETE');
        $response->assertSee('ASSIGN');
        $response->assertSee('LINK');
        $response->assertSee('EXPORT');
        $response->assertSee('Management');
        $response->assertSee('Super Admin');
        $response->assertSee('Permission Declined');
        $response->assertSee('#dc2626', false);
    }

    public function test_non_super_admin_cannot_access_permission_matrix(): void
    {
        $productManager = Admin::where('role', 'Product Manager')->first();
        $this->assertNotNull($productManager);

        $response = $this->actingAs($productManager, 'admin')
            ->get('/admin/security?tab=matrix');

        $response->assertStatus(403);

        $postResponse = $this->actingAs($productManager, 'admin')
            ->postJson('/admin/security/matrix', [
                'role' => 'Management',
                'permissions' => [],
            ]);

        $postResponse->assertStatus(403);
    }

    public function test_modifying_super_admin_permissions_triggers_permission_declined_exception(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/security/matrix', [
                'role' => 'Super Admin',
                'permissions' => [
                    'dashboard' => ['view' => false],
                    'products' => ['view' => false],
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Permission Declined',
        ]);
        $this->assertStringContainsString('Permission Declined', $response->json('error'));

        // Verify Super Admin still has full access unconditionally
        $this->assertTrue($this->admin->hasPermission('dashboard.view'));
        $this->assertTrue($this->admin->hasPermission('products.manage'));
        $this->assertTrue($this->admin->hasPermission('anything.arbitrary'));
    }

    public function test_super_admin_can_update_role_permissions_and_reflects_dynamically(): void
    {
        $productManager = Admin::where('role', 'Product Manager')->first();
        $this->assertNotNull($productManager);

        // Initially Product Manager has products.view
        $this->assertTrue($productManager->hasPermission('products.view'));

        // Super Admin revokes products.view and products.manage for Product Manager
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/security/matrix', [
                'role' => 'Product Manager',
                'permissions' => [
                    'products' => [
                        'view' => false,
                        'create' => false,
                        'edit_own' => false,
                        'edit_all' => false,
                        'delete' => false,
                        'assign' => false,
                        'link' => false,
                        'export' => false,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify dynamic permission revocation took effect
        $this->assertFalse($productManager->hasPermission('products.view'));
        $this->assertFalse($productManager->hasPermission('products.manage'));

        // Reset back to system defaults
        $resetResponse = $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/security/matrix', [
                'role' => 'Product Manager',
                'reset' => true,
            ]);

        $resetResponse->assertStatus(200);
        $this->assertTrue($productManager->hasPermission('products.view'));
    }

    public function test_product_details_shows_only_catalog_verified_and_flagged_filter_buttons(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.details'));

        $response->assertStatus(200);
        $response->assertSee('Catalog');
        $response->assertSee('Verified');
        $response->assertSee('Flagged');
        $response->assertSee('workflow-strip-filter');
        $response->assertSee('data-filter="catalog"', false);
        $response->assertSee('data-filter="verified"', false);
        $response->assertSee('data-filter="flagged"', false);

        // Assert Corrections and Duplicates cards are removed from top filter strip
        $response->assertDontSee('data-filter="corrections"', false);
        $response->assertDontSee('data-filter="duplicates"', false);

        // Test filtering by verified
        $resVerified = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.details', ['filter' => 'verified']));
        $resVerified->assertStatus(200);
        $resVerified->assertSee('workflow-step-btn active', false);

        // Test filtering by flagged
        $resFlagged = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.details', ['filter' => 'flagged']));
        $resFlagged->assertStatus(200);
    }
}
