<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\user\CartController;
use App\Http\Controllers\Api\user\ContactController;
use App\Http\Controllers\Api\user\OrderController;
use App\Http\Controllers\Api\user\UserCategoryController;
use App\Http\Controllers\Api\user\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =================================================== ADMIN MODULE AUTH
Route::prefix('auth/admin')->controller(AdminController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout/{admin_id}', 'logout');
});

// =================================================== ADMIN MODULE PROFILE
Route::prefix('admin/profile')->controller(AdminController::class)->group(function () {
    Route::get('/', 'showProfile');
    Route::patch('/update/{admin_id}', 'updateProfile');
    Route::patch('/update-password/{admin_id}', 'updatePassword');
    Route::delete('/destroy/{admin_id}', 'destroy');
});

// =================================================== ADMIN MODULE CATEGORIES
Route::prefix('admin/categories')->controller(CategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::patch('/update/{slug}', 'update');
    Route::get('/show/{slug}', 'show');
    Route::delete('/destroy/{slug}', 'destroy');
});

// =================================================== ADMIN MODULE PRODUCTS
Route::prefix('admin/products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::patch('/update/{slug}', 'update');
    Route::patch('/update-image/{id}', 'updateImage');
    Route::get('/show/{slug}', 'show');
    Route::delete('/destroy/{slug}', 'destroy');
});

// =================================================== ADMIN MODULE CONTACTS US
Route::controller(ContactController::class)->prefix('contacts/')->group(function () {
    Route::get('/', 'index');
    Route::get('/show/{id}', 'show');
    Route::delete('/destroy/{id}', 'destroy');
});

// ================================================================================================

// =================================================== USER MODULE AUTH
Route::prefix('auth/user')->controller(UserController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout/{user_id}', 'logout');
});

// =================================================== USER MODULE PROFILE
Route::prefix('user/profile')->controller(UserController::class)->group(function () {
    Route::get('/', 'showProfile');
    Route::patch('/update/{user_id}', 'updateProfile');
    Route::patch('/update-password/{user_id}', 'updatePassword');
    Route::delete('/destroy/{user_id}', 'destroy');
});

// =================================================== USER MODULE CATEGORIES
Route::prefix('user/categories')->controller(UserCategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}/products', 'showProducts');
});

// =================================================== USER MODULE CARTS
Route::prefix('cart')->controller(CartController::class)->group(function () {
    Route::post('/', 'addToCart');
    Route::get('/', 'getCartItems');
    Route::patch('/{id}', 'updateCartItem');
    Route::delete('/{id}', 'removeCartItem');
});

// =================================================== USER MODULE ORDERS
Route::middleware('auth.user')->group(function () {
    Route::post('/order', [OrderController::class, 'placeOrder']);
});

// =================================================== USER MODULE CONTACT US
Route::post('/contact/store', [ContactController::class, 'store']);
