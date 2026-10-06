<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Controllers\Admin\DuplicateController;
use App\Http\Controllers\Admin\HealthIntelligenceController;
use App\Http\Controllers\Admin\HealthConcernController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\AppContentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\AppControlController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\AuthController;

// Redirect root to admin dashboard (which guards will redirect to login if unauthenticated)
Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    // 0. Authentication Routes (Guest accessible)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes (Requires active admin session)
    Route::middleware('auth:admin')->group(function () {
        // 1. Dashboard Overview & Global Search
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [SearchController::class, 'search'])->name('search');

        // 2. Products Catalog & Views
        Route::get('/products/check-name', [ProductController::class, 'checkName'])->name('products.check-name')->middleware('permission:products.manage,products.edit');

        Route::middleware('permission:products.view')->group(function () {
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/product-details', [ProductController::class, 'details'])->name('products.details');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        });

        // Product Mutations & Verification
        Route::middleware('permission:products.manage')->group(function () {
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::post('/products/{product}/verify', [ProductController::class, 'verify'])->name('products.verify');
            Route::get('/verification-queue', [ProductController::class, 'verificationQueue'])->name('products.verification');
        });

        // Product Updates (Allowed for Product Managers & Submission Reviewers)
        Route::middleware('permission:products.manage,products.edit')->group(function () {
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        });

        // 3. Submissions (OCR Review Queue)
        Route::middleware('permission:submissions.view')->group(function () {
            Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        });
        Route::middleware('permission:submissions.manage')->group(function () {
            Route::post('/submissions/{submission}/review', [SubmissionController::class, 'review'])->name('submissions.review');
        });

        // 4. Product Corrections
        Route::middleware('permission:corrections.view')->group(function () {
            Route::get('/product-corrections', [CorrectionController::class, 'index'])->name('corrections.index');
            Route::get('/pending-corrections', [CorrectionController::class, 'pending'])->name('corrections.pending');
            Route::get('/product-corrections/{correction}', [CorrectionController::class, 'show'])->name('corrections.show');
        });
        Route::middleware('permission:corrections.manage')->group(function () {
            Route::post('/product-corrections/{correction}/review', [CorrectionController::class, 'review'])->name('corrections.review');
            Route::delete('/product-corrections/{correction}', [CorrectionController::class, 'destroy'])->name('corrections.destroy');
        });

        // 5. Duplicate Products Resolver
        Route::middleware('permission:duplicates.manage')->group(function () {
            Route::get('/duplicate-products', [DuplicateController::class, 'index'])->name('duplicates.index');
            Route::post('/duplicate-products/{duplicate}/resolve', [DuplicateController::class, 'resolve'])->name('duplicates.resolve');
        });

        // 6. Health Intelligence Rules
        Route::middleware('permission:health.view')->group(function () {
            Route::get('/health-intelligence', [HealthIntelligenceController::class, 'index'])->name('health.rules');
            Route::get('/health-intelligence/{rule}', [HealthIntelligenceController::class, 'show'])->name('health.rules.show');
            Route::get('/health-concerns', [HealthConcernController::class, 'index'])->name('health.concerns');
            Route::get('/health-concerns/{concern}', [HealthConcernController::class, 'show'])->name('health.concerns.show');
            Route::get('/personalized-alerts', [AlertController::class, 'index'])->name('health.alerts');
            Route::get('/personalized-alerts/{alert}', [AlertController::class, 'show'])->name('health.alerts.show');
        });
        Route::middleware('permission:health.manage')->group(function () {
            Route::post('/health-intelligence', [HealthIntelligenceController::class, 'store'])->name('health.rules.store');
            Route::put('/health-intelligence/{rule}', [HealthIntelligenceController::class, 'update'])->name('health.rules.update');
            Route::delete('/health-intelligence/{rule}', [HealthIntelligenceController::class, 'destroy'])->name('health.rules.destroy');
            Route::post('/health-concerns', [HealthConcernController::class, 'store'])->name('health.concerns.store');
            Route::put('/health-concerns/{concern}', [HealthConcernController::class, 'update'])->name('health.concerns.update');
            Route::post('/personalized-alerts', [AlertController::class, 'store'])->name('health.alerts.store');
        });

        // 7. Sponsored Ads & Promotions, Dynamic Content, Notifications
        Route::middleware('permission:marketing.view')->group(function () {
            Route::get('/ads', [CampaignController::class, 'index'])->name('ads.index');
            Route::get('/ads/{campaign}', [CampaignController::class, 'show'])->name('ads.show');
            Route::get('/content', [AppContentController::class, 'index'])->name('content.index');
            Route::get('/content/{content}', [AppContentController::class, 'show'])->name('content.show');
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
        });
        Route::middleware('permission:marketing.manage')->group(function () {
            Route::post('/ads', [CampaignController::class, 'store'])->name('ads.store');
            Route::put('/ads/{campaign}', [CampaignController::class, 'update'])->name('ads.update');
            Route::post('/ads/{campaign}/toggle', [CampaignController::class, 'toggle'])->name('ads.toggle');
            Route::post('/content', [AppContentController::class, 'store'])->name('content.store');
            Route::put('/content/{content}', [AppContentController::class, 'update'])->name('content.update');
            Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
        });

        // 8. Users & Contributors
        Route::middleware('permission:users.view')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        });
        Route::middleware('role:Super Admin')->group(function () {
            Route::post('/users/{user}/toggle-suspend', [UserController::class, 'toggleSuspend'])->name('users.toggleSuspend');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // 9. Master Data
        Route::middleware('permission:master_data.manage,master_data.view')->group(function () {
            Route::get('/master-data', [MasterDataController::class, 'index'])->name('masterData.index');
            Route::get('/master-data/{masterData}', [MasterDataController::class, 'show'])->name('masterData.show');
        });
        Route::middleware('permission:master_data.manage')->group(function () {
            Route::post('/master-data', [MasterDataController::class, 'store'])->name('masterData.store');
            Route::put('/master-data/{masterData}', [MasterDataController::class, 'update'])->name('masterData.update');
            Route::delete('/master-data/{masterData}', [MasterDataController::class, 'destroy'])->name('masterData.destroy');
        });

        // 10. Operations Exclusively for Super Admin
        Route::middleware('role:Super Admin')->group(function () {
            Route::get('/app-control', [AppControlController::class, 'index'])->name('appControl.index');
            Route::post('/app-control/update', [AppControlController::class, 'update'])->name('appControl.update');
            Route::post('/app-control/toggle', [AppControlController::class, 'toggle'])->name('appControl.toggle');

            Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
            Route::post('/security/matrix', [SecurityController::class, 'updateMatrix'])->name('security.matrix.update');
            Route::post('/security/admins', [SecurityController::class, 'storeAdmin'])->name('security.admins.store');
            Route::put('/security/admins/{admin}', [SecurityController::class, 'updateAdmin'])->name('security.admins.update');
            Route::delete('/security/admins/{admin}', [SecurityController::class, 'destroyAdmin'])->name('security.admins.destroy');
        });
    });
});
