<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\MasterData;
use App\Models\Product;
use App\Models\HealthConcern;
use App\Models\HealthRule;
use App\Models\PersonalizedAlert;
use App\Models\ProductSubmission;
use App\Models\ProductCorrection;
use App\Models\ProductDuplicate;
use App\Models\Campaign;
use App\Models\AppContent;
use App\Models\Notification;
use App\Models\AppControl;
use App\Models\AuditLog;

class ScanWellDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories & Brands
        $categories = ['Snacks', 'Breakfast Cereals', 'Dairy', 'Beverages', 'Protein Snacks', 'Condiments', 'Frozen Foods', 'Bakery'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat], ['slug' => Str::slug($cat), 'is_active' => true]);
        }

        $brands = ["Lay's", "Kellogg's", "Nature's Goodness", "Fresh Milk", "PureFuel", "Harvest Table", "Maggi"];
        foreach ($brands as $b) {
            Brand::firstOrCreate(['name' => $b], ['slug' => Str::slug($b), 'is_active' => true]);
        }

        // 2. Master Data Taxonomy
        $masterDataMap = [
            'Categories' => $categories,
            'Brands' => $brands,
            'Countries' => ['Bangladesh', 'India', 'United States', 'United Kingdom', 'United Arab Emirates', 'Australia'],
            'Nutrients' => ['Calories', 'Sugar', 'Added Sugar', 'Sodium', 'Total Fat', 'Saturated Fat', 'Trans Fat', 'Protein', 'Fiber', 'Carbohydrate', 'Potassium', 'Phosphorus'],
            'Units' => ['kcal', 'g', 'mg', 'mcg', 'ml', '% DV', 'per serving', 'per 100 g'],
            'Ingredients' => ['Whole grain oats', 'Honey', 'Milk solids', 'Almonds', 'Palm oil', 'Wheat flour', 'Cocoa butter'],
            'Additives' => ['INS 330', 'INS 331(iii)', 'INS 621', 'INS 631', 'INS 627', 'INS 551'],
            'Allergens' => ['Milk', 'Peanut', 'Tree Nut', 'Gluten', 'Soy', 'Egg', 'Fish', 'Shellfish'],
            'Health Concerns' => ['Diabetes', 'High Blood Pressure', 'Kidney Concern', 'Liver Concern', 'Asthma', 'Heart Concern', 'Food Allergy', 'High Cholesterol', 'Pregnancy', 'Other'],
        ];

        foreach ($masterDataMap as $type => $items) {
            foreach ($items as $item) {
                MasterData::firstOrCreate(['type' => $type, 'name' => $item], ['is_active' => true]);
            }
        }

        // 3. Admins
        $admins = [
            [
                'name' => env('ADMIN_SUPER_NAME', 'Nadia Karim'),
                'email' => env('ADMIN_SUPER_EMAIL', 'nadia@scanwell.app'),
                'password' => env('ADMIN_SUPER_PASSWORD', 'password123'),
                'role' => env('ADMIN_SUPER_ROLE', 'Super Admin'),
                'status' => 'Active',
                'last_login_at' => now()->subMinutes(25),
            ],
            [
                'name' => env('ADMIN_HEALTH_NAME', 'Amina Rahman'),
                'email' => env('ADMIN_HEALTH_EMAIL', 'amina@scanwell.app'),
                'password' => env('ADMIN_HEALTH_PASSWORD', 'password123'),
                'role' => env('ADMIN_HEALTH_ROLE', 'Health Content Reviewer'),
                'status' => 'Active',
                'last_login_at' => now()->subHours(1),
            ],
            [
                'name' => env('ADMIN_PRODUCT_NAME', 'Fahim Noor'),
                'email' => env('ADMIN_PRODUCT_EMAIL', 'fahim@scanwell.app'),
                'password' => env('ADMIN_PRODUCT_PASSWORD', 'password123'),
                'role' => env('ADMIN_PRODUCT_ROLE', 'Product Manager'),
                'status' => 'Active',
                'last_login_at' => now()->subHours(3),
            ],
            [
                'name' => 'Rina Das',
                'email' => 'rina@scanwell.app',
                'password' => 'password123',
                'role' => 'Marketing Manager',
                'status' => 'Active',
                'last_login_at' => now()->subDay(),
            ],
            [
                'name' => 'Tariq Hasan',
                'email' => 'submission@scanwell.app',
                'password' => 'password123',
                'role' => 'Submission Reviewer',
                'status' => 'Active',
                'last_login_at' => now()->subHours(4),
            ],
            [
                'name' => 'Samir Support',
                'email' => 'samir@scanwell.app',
                'password' => 'password123',
                'role' => 'Support Viewer',
                'status' => 'Active',
                'last_login_at' => now()->subHours(8),
            ],
        ];

        foreach ($admins as $adm) {
            Admin::firstOrCreate(
                ['email' => $adm['email']],
                [
                    'name' => $adm['name'],
                    'password' => Hash::make($adm['password']),
                    'role' => $adm['role'],
                    'status' => $adm['status'],
                    'last_login_at' => $adm['last_login_at'],
                ]
            );
        }

        // 4. Users (Consumers and Contributors)
        $users = [
            ['id' => 1, 'name' => 'Ammu Rahman', 'email' => 'ammu@example.com', 'role' => 'Consumer', 'status' => 'Active', 'scans_count' => 128, 'submissions_count' => 0, 'verified' => true, 'last_seen_at' => now()->subMinutes(2)],
            ['id' => 2, 'name' => 'Arif Hossain', 'email' => 'arif.h@example.com', 'role' => 'Contributor', 'status' => 'Active', 'scans_count' => 84, 'submissions_count' => 46, 'verified' => true, 'last_seen_at' => now()->subMinutes(11)],
            ['id' => 3, 'name' => 'Nadia Karim Contributor', 'email' => 'nadia.k@example.com', 'role' => 'Contributor', 'status' => 'Active', 'scans_count' => 61, 'submissions_count' => 72, 'verified' => true, 'last_seen_at' => now()->subMinutes(28)],
            ['id' => 4, 'name' => 'Samir Chowdhury', 'email' => 'samir.c@example.com', 'role' => 'Contributor', 'status' => 'Suspended', 'scans_count' => 25, 'submissions_count' => 9, 'verified' => false, 'last_seen_at' => now()->subDays(2)],
            ['id' => 5, 'name' => 'Tanna Jones', 'email' => 'tanna.j@example.com', 'role' => 'Consumer', 'status' => 'Active', 'scans_count' => 213, 'submissions_count' => 0, 'verified' => true, 'last_seen_at' => now()->subHour()],
            ['id' => 6, 'name' => 'Mehnaz Ali', 'email' => 'mehnaz.a@example.com', 'role' => 'Contributor', 'status' => 'Active', 'scans_count' => 92, 'submissions_count' => 31, 'verified' => true, 'last_seen_at' => now()->subHours(3)],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password123'),
                    'role' => $u['role'],
                    'status' => $u['status'],
                    'scans_count' => $u['scans_count'],
                    'submissions_count' => $u['submissions_count'],
                    'verified' => $u['verified'],
                    'last_seen_at' => $u['last_seen_at'],
                ]
            );
        }

        // 5. Products Catalog
        $products = [
            [
                'id' => 1,
                'name' => "Lay's American Style Cream & Onion",
                'brand' => "Lay's",
                'barcode' => '8901491101537',
                'category' => 'Snacks',
                'status' => 'Published',
                'verified' => true,
                'flags_count' => 2,
                'country' => 'India',
                'serving_size' => '28 g',
                'manufacturer' => 'PepsiCo India',
                'source' => 'Contributor + verified label',
                'ingredients' => 'Potato, edible vegetable oil, seasoning, salt, milk solids, permitted additives.',
                'nutrition' => ['Calories' => '160 kcal', 'Sugar' => '1 g', 'Sodium' => '200 mg', 'Total Fat' => '10 g', 'Protein' => '2 g'],
            ],
            [
                'id' => 2,
                'name' => 'Oats & Honey Granola',
                'brand' => "Nature's Goodness",
                'barcode' => '8901234567890',
                'category' => 'Breakfast Cereals',
                'status' => 'Draft',
                'verified' => false,
                'flags_count' => 3,
                'country' => 'India',
                'serving_size' => '40 g',
                'manufacturer' => 'Healthy Life Foods Pvt. Ltd',
                'source' => 'OCR submission',
                'ingredients' => 'Whole grain oats (54%), honey (15%), brown rice crisps, sunflower seeds, almond slices, raisins.',
                'nutrition' => ['Calories' => '190 kcal', 'Sugar' => '8 g', 'Sodium' => '210 mg', 'Total Fat' => '6 g', 'Protein' => '5 g'],
            ],
            [
                'id' => 3,
                'name' => 'Froot Loops',
                'brand' => "Kellogg's",
                'barcode' => '038000635344',
                'category' => 'Breakfast Cereals',
                'status' => 'Published',
                'verified' => true,
                'flags_count' => 2,
                'country' => 'United States',
                'serving_size' => '39 g',
                'manufacturer' => "Kellogg's",
                'source' => 'Brand dataset',
                'ingredients' => 'Corn flour blend, sugar, wheat flour, oat fiber, vegetable oil, natural and artificial flavor.',
                'nutrition' => ['Calories' => '150 kcal', 'Sugar' => '12 g', 'Sodium' => '210 mg', 'Total Fat' => '1.5 g', 'Protein' => '2 g'],
            ],
            [
                'id' => 4,
                'name' => 'French Milk Full Cream',
                'brand' => 'Fresh Milk',
                'barcode' => '6291107450014',
                'category' => 'Dairy',
                'status' => 'Published',
                'verified' => true,
                'flags_count' => 1,
                'country' => 'UAE',
                'serving_size' => '200 ml',
                'manufacturer' => 'Fresh Foods LLC',
                'source' => 'Admin import',
                'ingredients' => 'Fresh cow milk, vitamins A and D3.',
                'nutrition' => ['Calories' => '122 kcal', 'Sugar' => '9.4 g', 'Sodium' => '82 mg', 'Total Fat' => '6.8 g', 'Protein' => '6.4 g'],
            ],
            [
                'id' => 5,
                'name' => 'Almond Protein Bar',
                'brand' => 'PureFuel',
                'barcode' => '5060928441192',
                'category' => 'Protein Snacks',
                'status' => 'Archived',
                'verified' => false,
                'flags_count' => 1,
                'country' => 'United Kingdom',
                'serving_size' => '55 g',
                'manufacturer' => 'PureFuel Nutrition Ltd.',
                'source' => 'Legacy catalog',
                'ingredients' => 'Almonds, whey protein, dates, cocoa butter, natural flavor.',
                'nutrition' => ['Calories' => '214 kcal', 'Sugar' => '7 g', 'Sodium' => '95 mg', 'Total Fat' => '11 g', 'Protein' => '18 g'],
            ],
            [
                'id' => 6,
                'name' => 'Tomato Basil Crackers',
                'brand' => 'Harvest Table',
                'barcode' => '8906002215447',
                'category' => 'Snacks',
                'status' => 'Published',
                'verified' => true,
                'flags_count' => 2,
                'country' => 'India',
                'serving_size' => '30 g',
                'manufacturer' => 'Harvest Foods',
                'source' => 'Contributor',
                'ingredients' => 'Wheat flour, tomato powder, basil, palm oil, salt, raising agents.',
                'nutrition' => ['Calories' => '142 kcal', 'Sugar' => '3 g', 'Sodium' => '180 mg', 'Total Fat' => '5.2 g', 'Protein' => '3.1 g'],
            ]
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['id' => $p['id']], $p);
        }

        // 6. Health Concerns Taxonomy
        $concerns = [
            ['id' => 1, 'name' => 'Diabetes', 'icon' => 'pulse', 'description' => 'Blood sugar-focused product guidance', 'mapped_nutrients' => ['Sugar', 'Added Sugar', 'Carbohydrate'], 'active' => true],
            ['id' => 2, 'name' => 'High Blood Pressure', 'icon' => 'heart', 'description' => 'Sodium and cardiovascular caution signals', 'mapped_nutrients' => ['Sodium', 'Potassium'], 'active' => true],
            ['id' => 3, 'name' => 'Kidney Concern', 'icon' => 'shield', 'description' => 'Nutrient checks relevant to kidney support', 'mapped_nutrients' => ['Sodium', 'Potassium', 'Phosphorus'], 'active' => true],
            ['id' => 4, 'name' => 'Liver Concern', 'icon' => 'activity', 'description' => 'Fat, sugar and ingredient caution guidance', 'mapped_nutrients' => ['Added Sugar', 'Saturated Fat'], 'active' => true],
            ['id' => 5, 'name' => 'Food Allergy', 'icon' => 'warning', 'description' => 'Allergen and cross-contact warnings', 'mapped_nutrients' => ['Milk', 'Peanut', 'Tree Nut', 'Gluten'], 'active' => true],
            ['id' => 6, 'name' => 'High Cholesterol', 'icon' => 'heart', 'description' => 'Fat profile and heart-related guidance', 'mapped_nutrients' => ['Saturated Fat', 'Trans Fat'], 'active' => true],
            ['id' => 7, 'name' => 'Pregnancy', 'icon' => 'shield', 'description' => 'Configurable pregnancy-specific cautions', 'mapped_nutrients' => ['Caffeine', 'Vitamin A'], 'active' => false],
            ['id' => 8, 'name' => 'Asthma', 'icon' => 'activity', 'description' => 'Ingredient and additive sensitivity guidance', 'mapped_nutrients' => ['Sulphites', 'Preservatives'], 'active' => true],
            ['id' => 9, 'name' => 'Other', 'icon' => 'plus', 'description' => 'General preferences and future conditions', 'mapped_nutrients' => ['Custom rules'], 'active' => true],
        ];

        foreach ($concerns as $c) {
            HealthConcern::updateOrCreate(['id' => $c['id']], $c);
        }

        // 7. Health Rules
        $rules = [
            [
                'id' => 1,
                'name' => 'High sodium — general packaged food',
                'target' => 'Sodium',
                'operator' => '>=',
                'threshold' => '200',
                'unit' => 'mg / serving',
                'severity' => 'Red / High Concern',
                'health_concern_id' => 2,
                'concern' => 'High Blood Pressure',
                'status' => 'Published',
                'version' => '3.1',
                'effective_date' => 'Sep 01, 2026',
                'source' => 'ScanWell nutrition review guideline',
                'message' => 'This product has high sodium. Too much sodium may not be suitable for your health goal.',
                'recommendation' => 'Choose a lower-sodium alternative when possible.',
                'priority' => 95,
            ],
            [
                'id' => 2,
                'name' => 'Added sugar — diabetes caution',
                'target' => 'Added Sugar',
                'operator' => '>=',
                'threshold' => '5',
                'unit' => 'g / serving',
                'severity' => 'Yellow / Use With Caution',
                'health_concern_id' => 1,
                'concern' => 'Diabetes',
                'status' => 'Review',
                'version' => '2.4',
                'effective_date' => 'Sep 20, 2026',
                'source' => 'Internal clinical content review',
                'message' => 'Contains added sugar that may affect your blood sugar goal.',
                'recommendation' => 'Check serving size and total carbohydrate before choosing.',
                'priority' => 90,
            ],
            [
                'id' => 3,
                'name' => 'Zero trans fat positive flag',
                'target' => 'Trans Fat',
                'operator' => '=',
                'threshold' => '0',
                'unit' => 'g / serving',
                'severity' => 'Green / Looks Okay',
                'health_concern_id' => 6,
                'concern' => 'Heart Concern',
                'status' => 'Published',
                'version' => '1.8',
                'effective_date' => 'Aug 16, 2026',
                'source' => 'Nutrition label rule set',
                'message' => 'No trans fat declared per serving.',
                'recommendation' => 'Still review overall saturated fat and serving size.',
                'priority' => 50,
            ],
            [
                'id' => 4,
                'name' => 'MSG additive caution',
                'target' => 'INS 621',
                'operator' => 'contains',
                'threshold' => '',
                'unit' => '',
                'severity' => 'Yellow / Use With Caution',
                'health_concern_id' => 9,
                'concern' => 'Other',
                'status' => 'Draft',
                'version' => '0.7',
                'effective_date' => 'Not scheduled',
                'source' => 'Ingredient taxonomy review',
                'message' => 'This product contains monosodium glutamate (INS 621).',
                'recommendation' => 'Review ingredient preferences and sensitivities.',
                'priority' => 55,
            ],
            [
                'id' => 5,
                'name' => 'High saturated fat',
                'target' => 'Saturated Fat',
                'operator' => '>=',
                'threshold' => '5',
                'unit' => 'g / serving',
                'severity' => 'Red / High Concern',
                'health_concern_id' => 6,
                'concern' => 'High Cholesterol',
                'status' => 'Published',
                'version' => '2.1',
                'effective_date' => 'Sep 01, 2026',
                'source' => 'Clinical nutrition guidelines',
                'message' => 'High saturated fat declared per serving (>= 5g).',
                'recommendation' => 'Consider lower saturated fat alternatives to protect cardiovascular health.',
                'priority' => 80,
            ],
            [
                'id' => 6,
                'name' => 'High sugar content',
                'target' => 'Sugar',
                'operator' => '>=',
                'threshold' => '10',
                'unit' => 'g / serving',
                'severity' => 'Yellow / Use With Caution',
                'health_concern_id' => 1,
                'concern' => 'Diabetes',
                'status' => 'Published',
                'version' => '1.0',
                'effective_date' => 'Sep 15, 2026',
                'source' => 'Dietary sugar guideline',
                'message' => 'Total sugar exceeds 10g per serving, potentially impacting glycemic control.',
                'recommendation' => 'Check serving size and total carbohydrate balance.',
                'priority' => 85,
            ],
        ];

        foreach ($rules as $r) {
            HealthRule::updateOrCreate(['id' => $r['id']], $r);
        }

        // 8. Personalized In-App Alerts
        $alerts = [
            [
                'id' => 1,
                'condition' => 'Diabetes',
                'trigger' => 'Added Sugar >= 5 g',
                'severity' => 'Red',
                'title' => 'Better to avoid',
                'message' => 'Contains high sugar / added sugar.',
                'recommendation' => 'Choose an option with less added sugar.',
                'priority' => 1,
                'status' => 'Active',
                'destination' => 'product.health-flags',
            ],
            [
                'id' => 2,
                'condition' => 'Kidney Concern',
                'trigger' => 'Sodium >= 200 mg',
                'severity' => 'Yellow',
                'title' => 'Check before using',
                'message' => 'High sodium may not be suitable.',
                'recommendation' => 'Compare lower-sodium alternatives.',
                'priority' => 2,
                'status' => 'Active',
                'destination' => 'product.alternatives',
            ],
            [
                'id' => 3,
                'condition' => 'High Blood Pressure',
                'trigger' => 'Sodium >= 200 mg',
                'severity' => 'Green',
                'title' => 'Use with caution',
                'message' => 'Sodium level is high for your selected profile.',
                'recommendation' => 'Review serving size before consuming.',
                'priority' => 3,
                'status' => 'Draft',
                'destination' => 'product.nutrition',
            ],
            [
                'id' => 4,
                'condition' => 'Food Allergy',
                'trigger' => 'Contains Milk',
                'severity' => 'Red',
                'title' => 'Allergen detected',
                'message' => 'Milk is listed in the ingredient or allergen statement.',
                'recommendation' => 'Avoid if milk is one of your confirmed allergens.',
                'priority' => 1,
                'status' => 'Active',
                'destination' => 'product.ingredients',
            ]
        ];

        foreach ($alerts as $a) {
            PersonalizedAlert::updateOrCreate(['id' => $a['id']], $a);
        }

        // 9. OCR Submissions Queue
        $submissions = [
            [
                'id' => 'SW-10482',
                'product_name' => 'Oats & Honey Granola',
                'brand' => "Nature's Goodness",
                'barcode' => '8901234567890',
                'contributor' => 'Arif Hossain',
                'contributor_id' => 2,
                'confidence' => 92,
                'status' => 'Pending',
                'duplicate_check' => '87% match',
                'low_fields' => ['Category', 'Manufacturer', 'Added sugar', 'Sodium'],
                'extracted_fields' => ['Serving' => '40 g', 'Calories' => '190 kcal', 'Sodium' => '210 mg'],
                'submitted_at' => now()->subHours(2),
            ],
            [
                'id' => 'SW-10479',
                'product_name' => 'Mango Masala Noodles',
                'brand' => 'Maggi',
                'barcode' => '8901058845502',
                'contributor' => 'Nadia Karim Contributor',
                'contributor_id' => 3,
                'confidence' => 97,
                'status' => 'Review',
                'duplicate_check' => 'No close match',
                'low_fields' => ['Serving size'],
                'extracted_fields' => ['Serving' => '70 g', 'Calories' => '312 kcal'],
                'submitted_at' => now()->subHours(4),
            ],
            [
                'id' => 'SW-10475',
                'product_name' => 'French Milk Full Cream',
                'brand' => 'Fresh Milk',
                'barcode' => '6291107450014',
                'contributor' => 'Tariq Hasan',
                'contributor_id' => null,
                'confidence' => 88,
                'status' => 'Pending',
                'duplicate_check' => 'Possible duplicate',
                'low_fields' => ['Net weight', 'Protein', 'Ingredients'],
                'extracted_fields' => ['Volume' => '200 ml', 'Fat' => '6.8 g'],
                'submitted_at' => now()->subDay(),
            ],
            [
                'id' => 'SW-10470',
                'product_name' => 'Tomato Basil Crackers',
                'brand' => 'Harvest Table',
                'barcode' => '8906002215447',
                'contributor' => 'Mehnaz Ali',
                'contributor_id' => 6,
                'confidence' => 99,
                'status' => 'Approved',
                'duplicate_check' => 'None',
                'low_fields' => [],
                'extracted_fields' => ['Serving' => '30 g', 'Calories' => '142 kcal'],
                'submitted_at' => now()->subDays(2),
            ],
            [
                'id' => 'SW-10466',
                'product_name' => 'Almond Protein Bar',
                'brand' => 'PureFuel',
                'barcode' => '5060928441192',
                'contributor' => 'Samir Chowdhury',
                'contributor_id' => 4,
                'confidence' => 81,
                'status' => 'Rejected',
                'duplicate_check' => 'Exact barcode exists',
                'low_fields' => ['Product name', 'Sodium', 'Ingredients'],
                'extracted_fields' => ['Protein' => '18 g'],
                'submitted_at' => now()->subDays(3),
            ],
        ];

        foreach ($submissions as $s) {
            ProductSubmission::updateOrCreate(['id' => $s['id']], $s);
        }

        // 10. Product Corrections Queue
        $corrections = [
            [
                'id' => 'CR-2081',
                'product_id' => 2,
                'product_name' => 'Oats & Honey Granola',
                'field' => 'Sodium',
                'from_value' => '210 mg',
                'to_value' => '180 mg',
                'source' => 'Nutrition label photo',
                'requested_by' => 'Arif Hossain',
                'user_id' => 2,
                'status' => 'Pending',
                'risk' => 'Health-impacting',
                'submitted_at' => now()->subHours(1),
            ],
            [
                'id' => 'CR-2079',
                'product_id' => 3,
                'product_name' => 'Froot Loops',
                'field' => 'Ingredients',
                'from_value' => 'Previous ingredient list',
                'to_value' => 'Updated package ingredient list',
                'source' => 'New packaging photo',
                'requested_by' => 'Nadia Karim Contributor',
                'user_id' => 3,
                'status' => 'Review',
                'risk' => 'Content',
                'submitted_at' => now()->subHours(3),
            ],
            [
                'id' => 'CR-2076',
                'product_id' => 4,
                'product_name' => 'French Milk Full Cream',
                'field' => 'Serving size',
                'from_value' => '250 ml',
                'to_value' => '200 ml',
                'source' => 'Front label',
                'requested_by' => 'Tariq Hasan',
                'user_id' => null,
                'status' => 'Pending',
                'risk' => 'Nutrition',
                'submitted_at' => now()->subDay(),
            ],
            [
                'id' => 'CR-2071',
                'product_id' => 1,
                'product_name' => "Lay's American Style Cream & Onion",
                'field' => 'Product image',
                'from_value' => 'Legacy pack',
                'to_value' => 'Current pack',
                'source' => 'Contributor photo',
                'requested_by' => 'Mehnaz Ali',
                'user_id' => 6,
                'status' => 'Approved',
                'risk' => 'Visual',
                'submitted_at' => now()->subDays(2),
            ],
            [
                'id' => 'CR-2068',
                'product_id' => 6,
                'product_name' => 'Tomato Basil Crackers',
                'field' => 'Manufacturer',
                'from_value' => 'Harvest Foods',
                'to_value' => 'Harvest Table Foods Ltd.',
                'source' => 'Back label',
                'requested_by' => 'Samir Chowdhury',
                'user_id' => 4,
                'status' => 'Rejected',
                'risk' => 'Metadata',
                'submitted_at' => now()->subDays(3),
            ],
        ];

        foreach ($corrections as $c) {
            ProductCorrection::updateOrCreate(['id' => $c['id']], $c);
        }

        // 11. Duplicate Products Resolver
        $duplicates = [
            ['id' => 'DP-311', 'product_a_id' => 2, 'product_b_id' => null, 'name' => 'Oats & Honey Granola', 'match_percentage' => 91, 'reason' => 'Same barcode + similar nutrition', 'status' => 'Needs decision'],
            ['id' => 'DP-304', 'product_a_id' => 4, 'product_b_id' => null, 'name' => 'French Milk Full Cream', 'match_percentage' => 86, 'reason' => 'Same brand + package size', 'status' => 'Review'],
            ['id' => 'DP-299', 'product_a_id' => 6, 'product_b_id' => null, 'name' => 'Tomato Basil Crackers', 'match_percentage' => 82, 'reason' => 'Similar name + ingredients', 'status' => 'Review'],
        ];

        foreach ($duplicates as $d) {
            ProductDuplicate::updateOrCreate(['id' => $d['id']], $d);
        }

        // 12. Sponsored Campaigns (Ads)
        $campaigns = [
            [
                'id' => 1,
                'name' => 'Healthy Breakfast Week',
                'type' => 'Banner',
                'headline' => 'Start your morning with smarter choices',
                'copy' => 'Explore products with clearer nutrition and ingredient information.',
                'cta' => 'Explore products',
                'placement' => 'Home Banner',
                'start_at' => now()->subDays(2),
                'end_at' => now()->addDays(5),
                'priority' => 80,
                'frequency_cap' => 3,
                'status' => 'Active',
                'region' => 'All regions',
                'segment' => 'All users',
                'health_target' => 'None',
                'impressions' => 48230,
                'clicks' => 2641,
                'theme' => 'violet',
            ],
            [
                'id' => 2,
                'name' => 'Low Sodium Collection',
                'type' => 'Sponsored collection',
                'headline' => 'Looking for lower-sodium options?',
                'copy' => 'Browse a sponsored collection and compare labels before deciding.',
                'cta' => 'View collection',
                'placement' => 'Product Details',
                'start_at' => now()->subDays(5),
                'end_at' => now()->addDays(12),
                'priority' => 65,
                'frequency_cap' => 2,
                'status' => 'Active',
                'region' => 'Bangladesh',
                'segment' => 'Returning users',
                'health_target' => 'High Blood Pressure — contextual only',
                'impressions' => 21400,
                'clicks' => 820,
                'theme' => 'blue',
            ],
            [
                'id' => 3,
                'name' => 'Contributor Welcome',
                'type' => 'Card',
                'headline' => 'Help improve product information',
                'copy' => 'Verified contributors can submit clearer product labels and corrections.',
                'cta' => 'Learn more',
                'placement' => 'Home Feed',
                'start_at' => now()->addDays(3),
                'end_at' => now()->addDays(18),
                'priority' => 50,
                'frequency_cap' => 1,
                'status' => 'Scheduled',
                'region' => 'All regions',
                'segment' => 'Contributors',
                'health_target' => 'None',
                'impressions' => 0,
                'clicks' => 0,
                'theme' => 'green',
            ],
            [
                'id' => 4,
                'name' => 'Compare Before You Choose',
                'type' => 'Card',
                'headline' => 'Compare nutrition side by side',
                'copy' => 'Use ScanWell comparison tools when deciding between similar products.',
                'cta' => 'Compare products',
                'placement' => 'Search Results',
                'start_at' => now()->subDays(15),
                'end_at' => now()->subDay(),
                'priority' => 40,
                'frequency_cap' => 4,
                'status' => 'Paused',
                'region' => 'All regions',
                'segment' => 'Active scanners',
                'health_target' => 'None',
                'impressions' => 37600,
                'clicks' => 1125,
                'theme' => 'violet',
            ],
            [
                'id' => 5,
                'name' => 'Ingredient Awareness',
                'type' => 'Full-screen',
                'headline' => 'Know what is on the label',
                'copy' => 'Learn how ScanWell surfaces ingredients and additives from product labels.',
                'cta' => 'See how it works',
                'placement' => 'Full-screen Promotion',
                'start_at' => now()->addDays(15),
                'end_at' => now()->addDays(18),
                'priority' => 90,
                'frequency_cap' => 1,
                'status' => 'Draft',
                'region' => 'All regions',
                'segment' => 'New users',
                'health_target' => 'None',
                'impressions' => 0,
                'clicks' => 0,
                'theme' => 'blue',
            ],
        ];

        foreach ($campaigns as $camp) {
            Campaign::updateOrCreate(['id' => $camp['id']], $camp);
        }

        // 13. Dynamic App Content
        $contents = [
            [
                'id' => 1,
                'content_key' => 'onboarding.health_flags.title',
                'area' => 'Onboarding',
                'locale' => 'en-US',
                'title' => 'Understand health flags',
                'body' => 'See red, yellow, and green flags for sugar, sodium, fat, additives, allergens, and other concern areas.',
                'status' => 'Published',
                'editor' => 'Nadia Karim',
            ],
            [
                'id' => 2,
                'content_key' => 'onboarding.personalized.title',
                'area' => 'Onboarding',
                'locale' => 'en-US',
                'title' => 'Personalized for your health',
                'body' => 'Select your health concerns to get personalized warnings for diabetes, blood pressure, kidney, liver, asthma, allergy, and more.',
                'status' => 'Published',
                'editor' => 'Amina Rahman',
            ],
            [
                'id' => 3,
                'content_key' => 'onboarding.scan.title',
                'area' => 'Onboarding',
                'locale' => 'en-US',
                'title' => 'Scan packaged products',
                'body' => 'Scan barcode, nutrition label, and ingredients to quickly understand what is inside your food.',
                'status' => 'Published',
                'editor' => 'Amina Rahman',
            ],
            [
                'id' => 4,
                'content_key' => 'auth.login.helper',
                'area' => 'Authentication',
                'locale' => 'en-US',
                'title' => 'Welcome back',
                'body' => 'Log in to view scan history, saved products, and personalized health flags.',
                'status' => 'Published',
                'editor' => 'System Admin',
            ],
            [
                'id' => 5,
                'content_key' => 'scan.review.helper',
                'area' => 'Scan workflow',
                'locale' => 'en-US',
                'title' => 'Review your photo',
                'body' => 'Make sure the label is clear before continuing.',
                'status' => 'Draft',
                'editor' => 'Product Team',
            ],
            [
                'id' => 6,
                'content_key' => 'privacy.summary',
                'area' => 'Privacy',
                'locale' => 'en-US',
                'title' => 'Your health information is private',
                'body' => 'Profile choices are used to personalize your ScanWell experience. Advertising controls remain separated from health guidance.',
                'status' => 'Review',
                'editor' => 'Compliance Team',
            ],
            [
                'id' => 7,
                'content_key' => 'empty.saved_products',
                'area' => 'Empty state',
                'locale' => 'en-US',
                'title' => 'No saved products yet',
                'body' => 'Save products after scanning or searching to find them here later.',
                'status' => 'Published',
                'editor' => 'Nadia Karim',
            ]
        ];

        foreach ($contents as $cnt) {
            AppContent::updateOrCreate(['id' => $cnt['id']], $cnt);
        }

        // 14. Notifications
        $notifications = [
            [
                'id' => 1,
                'title' => 'Your saved product was updated',
                'body' => 'Nutrition information changed after a new label verification.',
                'audience' => 'Users who saved affected products',
                'schedule_time' => 'Sep 15, 6:00 PM',
                'deep_link' => 'scanwell://saved',
                'status' => 'Scheduled',
                'sent_count' => 0,
                'opened_count' => 0,
            ],
            [
                'id' => 2,
                'title' => 'New ScanWell comparison tools',
                'body' => 'Compare products side by side from search and product details.',
                'audience' => 'Active users (30 days)',
                'schedule_time' => 'Sep 12, 11:30 AM',
                'deep_link' => 'scanwell://compare',
                'status' => 'Sent',
                'sent_count' => 18420,
                'opened_count' => 4955,
            ],
            [
                'id' => 3,
                'title' => 'Complete your health profile',
                'body' => 'Select concerns to receive more relevant product alerts.',
                'audience' => 'Profiles without health concerns',
                'schedule_time' => 'Not scheduled',
                'deep_link' => 'scanwell://health-profile',
                'status' => 'Draft',
                'sent_count' => 0,
                'opened_count' => 0,
            ]
        ];

        foreach ($notifications as $notif) {
            Notification::updateOrCreate(['id' => $notif['id']], $notif);
        }

        // 15. App Control & Feature Flags
        $controls = [
            ['key' => 'maintenanceMode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Temporarily block normal app usage and show maintenance message'],
            ['key' => 'forceUpdate', 'value' => 'false', 'type' => 'boolean', 'description' => 'Require users below minimum version to update'],
            ['key' => 'registration', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow new users to create accounts'],
            ['key' => 'scanning', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow camera/barcode product scanning'],
            ['key' => 'ocr', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow label images to enter extraction processing'],
            ['key' => 'comparison', 'value' => 'true', 'type' => 'boolean', 'description' => 'Show side-by-side comparison tools'],
            ['key' => 'personalizedAlerts', 'value' => 'true', 'type' => 'boolean', 'description' => 'Show profile-based health guidance after opt-in'],
            ['key' => 'contributorMode', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow eligible users to submit product data & corrections'],
            ['key' => 'minVersion', 'value' => '1.4.0', 'type' => 'string', 'description' => 'Minimum supported mobile version'],
            ['key' => 'latestVersion', 'value' => '1.7.2', 'type' => 'string', 'description' => 'Latest published app version in stores'],
            ['key' => 'maintenanceMessage', 'value' => 'ScanWell is temporarily unavailable while we improve the service.', 'type' => 'string', 'description' => 'Maintenance message'],
        ];

        foreach ($controls as $ctrl) {
            AppControl::updateOrCreate(['key' => $ctrl['key']], $ctrl);
        }

        // 16. Audit Log
        $audits = [
            ['action' => 'Published health rule v3.1', 'detail' => 'High sodium — general packaged food', 'user' => 'Amina Rahman', 'status' => 'Published'],
            ['action' => 'Paused campaign', 'detail' => 'Compare Before You Choose', 'user' => 'Rina Das', 'status' => 'Paused'],
            ['action' => 'Approved submission SW-10470', 'detail' => 'Tomato Basil Crackers', 'user' => 'Fahim Noor', 'status' => 'Approved'],
            ['action' => 'Updated onboarding content', 'detail' => 'onboarding.personalized.title', 'user' => 'Nadia Karim', 'status' => 'Published'],
            ['action' => 'Changed app control', 'detail' => 'Minimum version 1.3.6 → 1.4.0', 'user' => 'Nadia Karim', 'status' => 'Active'],
        ];

        foreach ($audits as $audit) {
            AuditLog::create($audit);
        }
    }
}
