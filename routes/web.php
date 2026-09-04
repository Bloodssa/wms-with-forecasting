<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WarrantyForecastController;
use App\Http\Controllers\WarrantyServiceRecordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => Inertia::render('Index', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));
    Route::get('/about', fn() => Inertia::render('About', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));
    Route::get('/products', [ProductController::class, 'landingPageProducts'])->name('landing.products');
    Route::get('/products/details/{product}', [ProductController::class, 'landingPageProductsDetails'])->name('landing.details');

    // Google OAuth Route
    Route::get('/auth/google', [SocialiteController::class, 'googleLogin'])->name('auth.google');
    Route::get('/auth/google-callback', [SocialiteController::class, 'googleAuthentication'])->name('auth.google-callback');

    // Customer Registration Warranty Claim
    Route::get('/register/claim/{email}', [WarrantyController::class, 'show'])->name('customer.claim')->middleware('signed');
});

Route::middleware('auth', 'customer')->group(function () {
    Route::get('/home', [CustomerController::class, 'index'])->name('home');
    Route::get('/warranty', [CustomerController::class, 'warrantyList'])->name('warranty');
    Route::get('/inquiries', [CustomerController::class, 'inquiries'])->name('inquiries');
    Route::get('/inquiries/{id}', [CustomerController::class, 'showInquiry'])->name('inquiry.show');
    Route::get('/history', [CustomerController::class, 'history'])->name('history');
    Route::get('/warranty/{id}', [CustomerController::class, 'show'])->name('warranty.show');

    // products
    Route::get('/shop/products', [ProductController::class, 'products'])->name('view-products');
    Route::get('/shop/products/{product}', [ProductController::class, 'productsDetails'])->name('products-details');
    Route::get('/shop/products/{product}/reviews', [ProductReviewController::class, 'productReviews'])->name('product-reviews');
    Route::post('/shop/review/{product}', [ProductReviewController::class, 'store'])->name('store-review');
    Route::put('/shop/review/{review}', [ProductReviewController::class, 'update'])->name('update-review');

    Route::get('/make-inquiry/{id}', [WarrantyController::class, 'storeInquiry'])->name('create-inquiry');
    Route::post('/send-inquiry', [WarrantyController::class, 'inquire'])->name('inquire-warranty');
    Route::patch('/inquiry/{id}/cancel', [WarrantyController::class, 'cancelInquiry'])->name('inquiry-cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/warranty/claim', [WarrantyController::class, 'claimWithSerialNumber'])->name('warranty-claim');
});

Route::middleware('auth', 'manager')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'index'])->name('dashboard');
    Route::get('/register-warranty', [ManagerController::class, 'register'])->name('register-warranty');
    Route::get('/warranty-inquiries', [ManagerController::class, 'warrantyInquiries'])->name('warranty-inquiries');
    Route::get('/inquiry-response/{id}', [ManagerController::class, 'inquiryResponse'])->name('inquiry-action');
    Route::get('/warranties', [ManagerController::class, 'warranties'])->name('warranties');
    Route::get('/customers', [ManagerController::class, 'customers'])->name('customers');
    Route::get('/reports', [ManagerController::class, 'reports'])->name('reports');
    Route::get('/staff-accounts', [ManagerController::class, 'staffAccounts'])->name('staff-accounts');
    Route::get('/manager/profile', [ManagerController::class, 'profile'])->name('manager.profile');

    // Warranty
    Route::get('/invoice/download', [WarrantyController::class, 'downloadInvoice'])->name('warranty.download-invoice');
    Route::get('/warranties/{id}', [ManagerController::class, 'showWarranty'])->name('view-warranty');
    Route::post('/register-warranty', [WarrantyController::class, 'store'])->name('register-warranty-details');
    Route::post('/response', [WarrantyController::class, 'response'])->name('response');
    Route::patch('/warranty-status/{id}', [WarrantyController::class, 'update'])->name('inquiry-status');
    Route::post('/create-employee', [ManagerController::class, 'store'])->name('create-employee');
    Route::put('/warranties/{warranty}', [WarrantyController::class, 'updateWarranty'])->name('warranties.update');
    Route::put('/warranties/{warranty}/archive', [WarrantyController::class, 'archiveWarranty'])->name('warranties.archive');
    Route::put('/warranties/{warranty}/unarchive', [WarrantyController::class, 'unarchiveWarranty'])->name('warranties.unarchive');
    Route::delete('/warranties/{warranty}', [WarrantyController::class, 'destroyWarranty'])->name('warranties.destroy');

    // products
    Route::get('/manage-products', [ProductController::class, 'index'])->name('products');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('show.product');
    Route::post('/products', [ProductController::class, 'store'])->name('store-product');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('update-product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('delete-product');
    Route::put('/products/{review}/reply', [ProductReviewController::class, 'reply'])->name('review-reply');
    Route::put('/reviews/{review}/reply', [ProductReviewController::class, 'destroy'])->name('review-reply-delete');

    Route::post('/category', [CategoryController::class, 'store'])->name('store-category');
    Route::put('/category/{category}', [CategoryController::class, 'update'])->name('edit-category');
    Route::delete('/category/{category}', [CategoryController::class, 'destroy'])->name('delete-category');

    // update staffs
    Route::patch('/staff-accounts/{user}/role', [ManagerController::class, 'updateRole'])->name('staff.update-role');
    Route::delete('/staff-accounts/{user}', [ManagerController::class, 'destroyStaff'])->name('staff.destroy');

    // Generate PDF Report
    Route::get('/generate-report', [ManagerController::class, 'generateReport'])->name('generate');

    // Warranty Forecast
    Route::get('/warranty-forecast', [WarrantyForecastController::class, 'index'])->name('manager.warranty-forecast');
    Route::get('/warranty-forecast/products/{product}', [WarrantyForecastController::class, 'show'])->name('manager.warranty-forecast.show');
    // Recording an actual repair/service cost against an inquiry
    Route::post('/warranty-inquiries/{inquiry}/service-record', [WarrantyServiceRecordController::class, 'store'])
        ->name('inquiry.service-record.store');
});


Route::middleware('auth')->group(function () {
    // Add a policy.....
    Route::post('/send-response', [WarrantyController::class, 'response'])->name('inquiry-response');
    Route::post('/inquiry/{id}/mark-read', [WarrantyController::class, 'markRead'])->name('inquiry.mark-read');
    Route::post('/notifications/read', [WarrantyController::class, 'markReadNotifications'])->name('notifications.read');
    Route::put('/profile/set-password', [ProfileController::class, 'setPassword'])->name('profile.set-password');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
