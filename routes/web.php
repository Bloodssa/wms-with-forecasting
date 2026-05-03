<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// use App\Mail\WarrantyInvitation;
// // use App\Mail\WarrantyNearExpiry;
// // use App\Mail\WarrantyExpired;
// use App\Models\Warranty;

// // // Route to view the email that will be sent to the user
// Route::get('/preview-mail', function () {

//     $warranty = Warranty::whereNotNull('user_id')->get();

//     return new WarrantyInvitation($warranty, 'test@gmail.com','');
//     // return new WarrantyNearExpiry($warranty);
//     // return new WarrantyExpired($warranty);
// });

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => Inertia::render('Index', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));
    Route::get('/about', fn() => Inertia::render('About', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));
    Route::get('/products   ', fn() => Inertia::render('Products', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));
    Route::get('/faq', fn() => Inertia::render('Faq', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]));

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
    Route::get('/shop/products/{id}', [ProductController::class, 'productsDetails'])->name('products-details');
    Route::get('/shop/products/{id}/reviews', [ProductController::class, 'productReviews'])->name('product-reviews');
    Route::post('/shop/review/{id}', [ProductController::class, 'storeReview'])->name('store-review');
    Route::put('/shop/review/{review}', [ProductController::class, 'updateReview'])->name('update-review');

    Route::get('/make-inquiry/{id}', [WarrantyController::class, 'storeInquiry'])->name('create-inquiry');
    Route::post('/send-inquiry', [WarrantyController::class, 'inquire'])->name('inquire-warranty');
    Route::patch('/inquiry/{id}/cancel', [WarrantyController::class, 'cancelInquiry'])->name('inquiry-cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/warranty/claim', [WarrantyController::class, 'claimWithSerialNumber'])->name('warranty-claim');
});

Route::middleware('auth', 'manager')->group(function() {
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
    Route::get('/warranties/{id}', [ManagerController::class, 'showWarranty'])->name('view-warranty');
    Route::post('/register-warranty', [WarrantyController::class, 'store'])->name('register-warranty-details');
    Route::post('/response', [WarrantyController::class, 'response'])->name('response');
    Route::patch('/warranty-status/{id}', [WarrantyController::class, 'update'])->name('inquiry-status');
    Route::post('/create-employee', [ManagerController::class, 'store'])->name('create-employee');

    // products
    Route::get('/manage-products', [ProductController::class, 'index'])->name('products');
    Route::get('/products/{id}', [ProductController::class, 'showProduct'])->name('show.product');
    Route::post('/products', [ProductController::class, 'store'])->name('store-product');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('update-product');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('delete-product');
    Route::put('/products/{review}/reply', [ProductController::class, 'reviewReply'])->name('review-reply');
    Route::delete('/reviews/{review}/reply', [ProductController::class, 'deleteReply'])->name('review-reply-delete');

    Route::post('/category', [ProductController::class, 'storeCategory'])->name('store-category');
    Route::put('/category/{id}', [ProductController::class, 'updateCategory'])->name('edit-category');
    Route::delete('/category/{id}', [ProductController::class, 'destroyCategory'])->name('delete-category');

    // update staffs
    Route::patch('/staff-accounts/{user}/role', [ManagerController::class, 'updateRole'])->name('staff.update-role');
    Route::delete('/staff-accounts/{user}', [ManagerController::class, 'destroyStaff'])->name('staff.destroy');
    
    // Generate PDF Report
    Route::get('/generate-report', [ManagerController::class, 'generateReport'])->name('generate');
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
