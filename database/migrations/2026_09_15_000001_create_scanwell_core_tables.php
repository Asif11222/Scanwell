<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Extend Users table for ScanWell Consumers & Community Contributors
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Consumer')->after('email'); // 'Consumer', 'Contributor'
            $table->string('status')->default('Active')->after('role'); // 'Active', 'Suspended'
            $table->unsignedInteger('scans_count')->default(0)->after('status');
            $table->unsignedInteger('submissions_count')->default(0)->after('scans_count');
            $table->boolean('verified')->default(true)->after('submissions_count');
            $table->timestamp('last_seen_at')->nullable()->after('verified');
        });

        // 2. Admins & Roles
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('Product Manager'); // Super Admin, Health Content Reviewer, Product Manager, Submission Reviewer, Marketing Manager, Support Viewer
            $table->string('status')->default('Active'); // Active, Suspended
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Master Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Master Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. General Master Data (Nutrients, Units, Ingredients, Additives, Allergens, Countries)
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Categories, Brands, Countries, Nutrients, Units, Ingredients, Additives, Allergens, Health Concerns
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        // 6. Products Catalog
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('barcode')->unique()->index();
            $table->string('category')->index();
            $table->string('status')->default('Draft')->index(); // Published, Draft, Archived
            $table->boolean('verified')->default(false)->index();
            $table->unsignedSmallInteger('flags_count')->default(0);
            $table->string('country')->nullable();
            $table->string('serving_size')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('source')->nullable(); // Contributor, OCR submission, Brand dataset, Admin import, Legacy catalog
            $table->text('ingredients')->nullable();
            $table->json('nutrition')->nullable(); // Calories, Sugar, Added Sugar, Sodium, Total Fat, Saturated Fat, Trans Fat, Protein, Fiber, Carbohydrate, Potassium, Phosphorus
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // 7. Health Concerns Taxonomy (Diabetes, High Blood Pressure, Kidney, Allergies, etc.)
        Schema::create('health_concerns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->default('heart');
            $table->text('description')->nullable();
            $table->json('mapped_nutrients')->nullable(); // Array of strings e.g. ['Sugar', 'Added Sugar', 'Carbohydrate']
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 8. Health Intelligence Rules
        Schema::create('health_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target'); // Sodium, Added Sugar, Trans Fat, INS 621, Saturated Fat, etc.
            $table->string('operator'); // >=, >, <=, <, =, contains
            $table->string('threshold')->nullable(); // 200, 5, 0, etc.
            $table->string('unit')->nullable(); // mg / serving, g / serving, etc.
            $table->string('severity'); // Red / High Concern, Yellow / Use With Caution, Green / Looks Okay
            $table->foreignId('health_concern_id')->nullable()->constrained('health_concerns')->nullOnDelete();
            $table->string('concern')->nullable(); // High Blood Pressure, Diabetes, etc.
            $table->string('status')->default('Draft')->index(); // Draft, Review, Published, Archived
            $table->string('version')->default('1.0');
            $table->string('effective_date')->nullable();
            $table->string('source')->nullable();
            $table->text('message');
            $table->text('recommendation')->nullable();
            $table->unsignedSmallInteger('priority')->default(50);
            $table->timestamps();
        });

        // 9. Personalized In-App Alerts
        Schema::create('personalized_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('condition'); // Diabetes, Kidney Concern, High Blood Pressure, Food Allergy
            $table->string('trigger'); // Added Sugar >= 5 g, Sodium >= 200 mg, Contains Milk
            $table->string('severity')->default('Yellow'); // Red, Yellow, Green
            $table->string('title');
            $table->text('message');
            $table->text('recommendation')->nullable();
            $table->unsignedSmallInteger('priority')->default(1);
            $table->string('status')->default('Active')->index(); // Active, Draft
            $table->string('destination')->nullable(); // product.health-flags, product.alternatives, product.nutrition, product.ingredients
            $table->timestamps();
        });

        // 10. OCR / Mobile Scan Submissions Queue
        Schema::create('product_submissions', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. SW-10482
            $table->string('product_name');
            $table->string('brand');
            $table->string('barcode')->index();
            $table->string('contributor');
            $table->foreignId('contributor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('confidence')->default(90); // OCR confidence %
            $table->string('status')->default('Pending')->index(); // Pending, Review, Approved, Rejected
            $table->string('duplicate_check')->nullable(); // '87% match', 'No close match', 'Possible duplicate', 'None'
            $table->json('low_fields')->nullable(); // Array of field names that need closer human review
            $table->json('extracted_fields')->nullable(); // Full map of extracted nutrition/metadata
            $table->string('label_image')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 11. Product Corrections Queue
        Schema::create('product_corrections', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. CR-2081
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('field'); // Sodium, Ingredients, Serving size, Product image, Manufacturer
            $table->text('from_value')->nullable();
            $table->text('to_value')->nullable();
            $table->string('source')->nullable(); // Nutrition label photo, New packaging photo, Front label, Contributor photo, Back label
            $table->string('requested_by');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('Pending')->index(); // Pending, Review, Approved, Rejected
            $table->string('risk')->default('Content'); // Health-impacting, Content, Nutrition, Visual, Metadata
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 12. Duplicate Products Resolver
        Schema::create('product_duplicates', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. DP-311
            $table->foreignId('product_a_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_b_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('match_percentage')->default(80); // e.g. 91%
            $table->string('reason'); // Same barcode + similar nutrition, Same brand + package size, Similar name + ingredients
            $table->string('status')->default('Review')->index(); // Needs decision, Review, Resolved
            $table->timestamps();
        });

        // 13. Ads & Promotional Campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('Banner'); // Banner, Card, Sponsored collection, Full-screen
            $table->string('headline');
            $table->text('copy');
            $table->string('cta')->default('Learn more');
            $table->string('cta_url')->default('scanwell://search');
            $table->string('placement')->default('Home Banner'); // Home Banner, Home Feed, Search Results, Product Details, Scan Result, Personalized Alerts, Full-screen Promotion
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedSmallInteger('priority')->default(50);
            $table->unsignedTinyInteger('frequency_cap')->default(1);
            $table->string('status')->default('Draft')->index(); // Active, Scheduled, Paused, Draft, Archived
            $table->string('region')->default('All regions');
            $table->string('segment')->default('All users'); // All users, New users, Returning users, Active scanners, Contributors
            $table->string('health_target')->default('None');
            $table->string('theme')->default('violet'); // violet, blue, green
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });

        // 14. In-App Dynamic Content (Strings, Onboarding, Privacy, Empty States)
        Schema::create('app_contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_key')->unique(); // e.g. onboarding.health_flags.title
            $table->string('area'); // Onboarding, Authentication, Scan workflow, Privacy, Empty state, Help, Banner
            $table->string('locale')->default('en-US');
            $table->string('title');
            $table->text('body');
            $table->string('status')->default('Draft')->index(); // Published, Review, Draft
            $table->string('editor')->nullable();
            $table->timestamps();
        });

        // 15. Push Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('audience'); // Users who saved affected products, Active users (30 days), Profiles without health concerns, Contributors
            $table->string('schedule_time')->nullable();
            $table->string('deep_link')->default('scanwell://home');
            $table->string('status')->default('Draft')->index(); // Scheduled, Sent, Draft
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->timestamps();
        });

        // 16. App Control & Feature Flags (Key-Value Runtime Config)
        Schema::create('app_controls', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('boolean'); // boolean, string, integer, json
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 17. Security Audit Log
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->text('detail')->nullable();
            $table->string('user');
            $table->string('status')->default('Active'); // Published, Paused, Approved, Active
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('app_controls');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('app_contents');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('product_duplicates');
        Schema::dropIfExists('product_corrections');
        Schema::dropIfExists('product_submissions');
        Schema::dropIfExists('personalized_alerts');
        Schema::dropIfExists('health_rules');
        Schema::dropIfExists('health_concerns');
        Schema::dropIfExists('products');
        Schema::dropIfExists('master_data');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('admins');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'scans_count', 'submissions_count', 'verified', 'last_seen_at']);
        });
    }
};
