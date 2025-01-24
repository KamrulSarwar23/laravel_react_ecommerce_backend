<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\frontend\ProductController as FrontendProductController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'authenticate']);

Route::get('/get-latest-products', [FrontendProductController::class, 'latestProduct']);
Route::get('/get-featured-products', [FrontendProductController::class, 'featuredProduct']);
Route::get('/get-all-products', [FrontendProductController::class, 'getAllProducts']);
Route::get('/categories-by-product', [FrontendProductController::class, 'getCategories']);
Route::get('/brands-by-products', [FrontendProductController::class, 'getBrands']);
Route::get('/products-details/{id}', [FrontendProductController::class, 'productDetails']);
Route::post('/products-filter-by-category-and-brand', [FrontendProductController::class, 'productFilterByCategoryAndBrand']);

Route::group(['middleware' => 'auth:sanctum'], function () {

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

    Route::get('/sizes', [SizeController::class, 'index']);

});
