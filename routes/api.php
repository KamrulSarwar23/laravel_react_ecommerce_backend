<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CartController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\CustomerAuthController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\frontend\OrderController;
use App\Http\Controllers\frontend\ProductController as FrontendProductController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/customer/login', [CustomerAuthController::class, 'customerLogin']);
Route::post('/register', [CustomerAuthController::class, 'register']);

Route::get('/get-latest-products', [FrontendProductController::class, 'latestProduct']);
Route::get('/get-featured-products', [FrontendProductController::class, 'featuredProduct']);
Route::get('/categories-by-product', [FrontendProductController::class, 'getCategories']);
Route::get('/brands-by-products', [FrontendProductController::class, 'getBrands']);
Route::get('/get-all-products', [FrontendProductController::class, 'getAllProducts']);
Route::get('/products-details/{id}', [FrontendProductController::class, 'productDetails']);
Route::get('/product-by-category/{id}', [FrontendProductController::class, 'categoryProduct']);
Route::get('/suggested-products/{id}', [FrontendProductController::class, 'suggestedProducts']);



Route::middleware('auth:sanctum', 'role:customer')->group(function () {

    Route::get('/customer/logout', [CustomerAuthController::class, 'CustomerLogout']);
    Route::get('/customer/user', [CustomerAuthController::class, 'getCustomer']);
    Route::put('/customer/profile', [CustomerAuthController::class, 'customerUpdateProfile']);
    Route::put('/customer/update-password', [CustomerAuthController::class, 'customerUpdatePassword']);


    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::get('/remove/cart/{id}', [CartController::class, 'removeCart']);
    Route::put('/cart/update', [CartController::class, 'updateCartQuantity']);
    Route::get('/cart/count', [CartController::class, 'cartCount']);

    Route::post('/pay-with-cash-on-delivery', [OrderController::class, 'CashOnDelivery']);

    Route::get('/customer-order-list', [OrderController::class, 'CustomerOrderList']);
    Route::get('/customer-invoice/{id}', [OrderController::class, 'CustomerInvoice']);


});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {


    Route::get('/admin/user', [AuthController::class, 'getUser']);
    Route::put('/admin/profile', [AuthController::class, 'updateProfile']);
    Route::put('/admin/update-password', [AuthController::class, 'updatePassword']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/user-list', [DashboardController::class, 'userList']);
    Route::get('/order-list', [DashboardController::class, 'OrderList']);
    Route::get('/transaction-list', [DashboardController::class, 'TransactionList']);
    Route::get('/invoice/{id}', [DashboardController::class, 'Invoice']);

    Route::put('/change-payment-status/{id}', [DashboardController::class, 'ChangePaymentStatus']);

    Route::put('/change-order-status/{id}', [DashboardController::class, 'ChangeOrderStatus']);

    Route::get('/dashboard-analytics', [DashboardController::class, 'dashboardAnalytics']);

    // Category Routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Brands Routes
    Route::get('/brands', [BrandController::class, 'index']);
    Route::post('/brands', [BrandController::class, 'store']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);
    Route::put('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);



    // Products Routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/get-categories', [ProductController::class, 'getCategories']);
    Route::get('/get-brands', [ProductController::class, 'getBrands']);
    Route::post('/temp-images', [TempImageController::class, 'store']);
    Route::post('/save-product-image', [ProductController::class, 'saveProductImage']);
    Route::get('/change-product-default-image', [ProductController::class, 'updateDefaultImage']);
    Route::get('/delete-image-while-update/{id}', [ProductController::class, 'removeImageWhileUpdate']);
    Route::post('/change-product-status/{id}', [ProductController::class, 'changeProductStatus']);
    Route::post('/change-product-is_featured/{id}', [ProductController::class, 'changeProductIsFeatured']);

    Route::get('/sizes', [SizeController::class, 'index']);
});
