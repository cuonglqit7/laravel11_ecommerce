<?php

use App\Http\Controllers\API\ArticleCategoryController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DiscountController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductAttributeController;
use App\Http\Controllers\API\ProductFavoriteController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\ArticleImageController;
use App\Http\Controllers\ProductReviewController;
use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;


Route::get('/api/documentation', [SwaggerController::class, 'api']);

Route::prefix('v1')->group(function () {
    //người dùng
    Route::post('/login', [AuthAPIController::class, 'login']);
    Route::post('/register', [AuthAPIController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    //banner
    Route::get('/banners', [BannerController::class, 'getAll']);

    //danh mục sản phẩm
    Route::get('/categories', [CategoryController::class, 'getAll']);
    Route::get('/categories/parents', [CategoryController::class, 'getParentCategories']);
    Route::get('/categories/{parent_id}/children', [CategoryController::class, 'getChildCategories']);

    //sản phẩm
    Route::get('/products', [ProductController::class, 'getAll']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Route::get('/products/best-selling', [ProductController::class, 'getBestSellingProducts']);

    //hình ảnh sản phẩm
    Route::get('/products/{product_id}/avatar', [ProductImageController::class, 'getAvatar']);
    Route::get('/products/{product_id}/images', [ProductImageController::class, 'getAll']);

    //thuộc tính sản phẩm
    Route::get('/products/{product_id}/attributes', [ProductAttributeController::class, 'getAttributesByProduct']);
    Route::get('/products/attributes', [ProductAttributeController::class, 'getAllGroupedAttributes']);

    //danh mục bài viết
    Route::get('/articleCategories', [ArticleCategoryController::class, 'getAll']);

    //bài viết
    Route::get('/articles', [ArticleController::class, 'getAll']);
    Route::get('/articles/{id}', [ArticleController::class, 'getById']);



    //payment
    Route::post('/payments', [PaymentController::class, 'createPayment']);
    Route::put('/payments/{id}', [PaymentController::class, 'updatePaymentStatus']);

    //giảm giá
    Route::post('/discounts/check', [DiscountController::class, 'check']);
    Route::get('/discounts', [DiscountController::class, 'getAllDiscounts']);
    Route::get('/discounts/{id}', [DiscountController::class, 'getDiscountById']);

    Route::middleware('auth:sanctum')->group(function () {
        //người dùng
        Route::get('/user', [UserAPIController::class, 'show']);
        Route::put('/user/{id}', [UserAPIController::class, 'update']);
        Route::post('/logout', [AuthAPIController::class, 'logout']);

        //sản phẩm yêu thích
        Route::get('/favorites', [ProductFavoriteController::class, 'getUserFavorites']);
        Route::post('/favorites', [ProductFavoriteController::class, 'addToFavorites']);
        Route::delete('/favorites', [ProductFavoriteController::class, 'removeFromFavorites']);
        Route::get('/favorites/{id}/check', [ProductFavoriteController::class, 'isFavorite']);

        //đặt hàng
        Route::post('/orders', [OrderController::class, 'createOrder']);
        Route::post('/cancelOrder/{id}', [OrderController::class, 'cancelOrder']);

        //đơn hàng
        Route::get('/orders', [OrderController::class, 'getOrderByIp']);

        //Đánh giá sản phẩm
        Route::prefix('reviews')->group(function () {
            Route::get('/products/{product_id}/reviews', [ProductReviewController::class, 'getReviewsByProduct']);
            Route::post('/', [ProductReviewController::class, 'createReview']);
            Route::put('/{id}', [ProductReviewController::class, 'updateReview']);
            Route::delete('/{id}', [ProductReviewController::class, 'deleteReview']);
        });
    });

    Route::middleware('auth:sanctum')->post('/upload-articleImage', [ArticleImageController::class, 'upload']);
});

Route::prefix('v2')->group(function () {
    Route::get('/products/best-selling', [ProductController::class, 'getBestSellingProducts']);
    Route::get('/products/{id}', [ProductController::class, 'getById']);
});
