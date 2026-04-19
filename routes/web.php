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
    Route::get('/register/claim/{serial}', [WarrantyController::class, 'show'])->name('customer.claim')->middleware('signed');
});

Route::middleware('auth', 'customer')->group(function () {
    Route::get('/home', [CustomerController::class, 'index'])->name('home');
    Route::get('/warranty', [CustomerController::class, 'warrantyList'])->name('warranty');
    Route::get('/inquiries', [CustomerController::class, 'inquiries'])->name('inquiries');
    Route::get('/view-products', [CustomerController::class, 'products'])->name('view-products');
    Route::get('/history', [CustomerController::class, 'history'])->name('history');
    Route::get('/warranty/{id}', [CustomerController::class, 'show'])->name('warranty.show');

    Route::post('/send-inquiry', [WarrantyController::class, 'inquire'])->name('inquire-warranty'); 
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
    Route::post('/register-warranty', [WarrantyController::class, 'store'])->name('register-warranty-details');
    Route::post('/response', [WarrantyController::class, 'response'])->name('response');
    Route::patch('/warranty-status/{id}', [WarrantyController::class, 'update'])->name('inquiry-status');
    Route::post('/create-employee', [ManagerController::class, 'store'])->name('create-employee');

    Route::get('/manage-products', [ProductController::class, 'index'])->name('products');

    
    // Generate PDF Report
    Route::get('/generate-report', [ManagerController::class, 'generateReport'])->name('generate');
});

/**
 * Breeze
 */


Route::middleware('auth')->group(function () {
    // Add a policy.....
    Route::post('/send-response', [WarrantyController::class, 'response'])->name('inquiry-response');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
