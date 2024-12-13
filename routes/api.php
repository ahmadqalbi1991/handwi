<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MyShopController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Traits\SMSTrait;


Route::namespace('App\Http\Controllers\Api\v1')->prefix("v1")->name('api.v1.')->middleware(['verify_api'])->group(function () {
    Route::post('/test-notification', [UserController::class, 'testNotification']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('social_login', [LoginController::class, 'social_login']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware(['auth:api']);
    
    Route::prefix('products')->name('products.')->group(function () {
        Route::post('/get-product-info', [ProductController::class, 'getProductInfo']);
        Route::post('/toggle-favourite', [ProductController::class, 'toggleFavourite'])->middleware(['auth:api']);
        Route::post('/get-order-by-product-ticket', [ProductController::class, 'getOrderByProductTicket'])->middleware(['auth:api']);
        Route::post('/get-closed-campaign-by-category', [ProductController::class, 'getClosedCampaignByCategory'])->middleware(['auth:api']);
        Route::post('/get-product-list-by-category-id', [ProductController::class, 'getProductListByCategoryId'])->middleware(['auth:api']);
        Route::post('/home', [ProductController::class, 'home']);
        Route::post('/get-all-products', [ProductController::class, 'getAllProducts']);
        Route::post('/search-products', [ProductController::class, 'searchProducts'])->middleware(['auth:api']);
        Route::post('/get-featured-products', [ProductController::class, 'getFeaturedProducts']);
        Route::post('/get-all-winners', [ProductController::class, 'getAllWinners']);
    });
    Route::prefix('home')->name('home.')->group(function () {
        Route::post('/cms-content', [HomeController::class, 'CMSContent']);
        Route::post('/get-cities', [HomeController::class, 'getCities']);
        Route::post('/get-countries', [HomeController::class, 'getCountries']);
        Route::post('/contact-us/submit', [HomeController::class, 'submitContactUs']);
        Route::post('/faq', [HomeController::class, 'faqs']);
    });
    Route::prefix('spinners')->name('spinners.')->group(function () {
        Route::post('/prize-list', [HomeController::class, 'prizeList']);
        Route::post('/get-my-spinners', [UserController::class, 'getMySpinners'])->middleware(['auth:api']);
        Route::post('/spinner-save-result', [UserController::class, 'saveSpinnerResult'])->middleware(['auth:api']);
    });
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::post('/get-order-ticket-numbers', [OrdersController::class, 'getOrderTicketNumbers']);
        Route::post('/get-campaign-draw-details', [OrdersController::class, 'getCampaignDrawDetails'])->middleware(['auth:api']);
        Route::post('/get-my-tickets', [OrdersController::class, 'getMyTickets'])->middleware(['auth:api']);
    });
    Route::prefix('my-shop')->name('my-shop.')->middleware(['auth:api'])->group(function () {
        Route::post('/delete-product-shop', [MyShopController::class, 'deleteProductShop']);
        Route::post('/delete-all-shop-products', [MyShopController::class, 'deleteAllShopProducts']);
        Route::post('/clear-favourites', [MyShopController::class, 'clearFavourites']);
        Route::post('/toggle-shop', [MyShopController::class, 'toggleShop']);
        Route::post('/get-all-shop-products-list', [MyShopController::class, 'getAllShopProductsList']);
        Route::post('/get-shop', [MyShopController::class, 'getShop']);
        Route::post('/claim-marks', [MyShopController::class, 'claimMarks']);
    });
    Route::prefix('cart')->name('cart.')->middleware(['auth:api'])->group(function () {
        Route::post('/donate-products', [CartController::class, 'donateProducts']);
        Route::post('/add-to-cart', [CartController::class, 'addToCart']);
        Route::post('/place-order', [CartController::class, 'placeOrder']);
        Route::post('/checkout', [CartController::class, 'checkout']);
        Route::post('/get-cart', [CartController::class, 'getCart']);
        Route::post('/apply-promo-code', [CartController::class, 'applyCode'])->middleware(['auth:api']);
        Route::post('/reduce-cart', [CartController::class, 'reduceCart']);
        Route::post('/reduce-cart-by-device-cart-id', [CartController::class, 'reduceCartByDevice_cartId'])->withoutMiddleware(['auth:api']);
        Route::post('/delete-products-from-cart', [CartController::class, 'deleteProductsFromCart']);
        Route::post('/delete-products-from-cart-by-device-cart-id', [CartController::class, 'deleteProductsFromCartByDeviceCartId'])->withoutMiddleware(['auth:api']);
        Route::post('/payment-init', [CartController::class, 'createStripePayment']);
        Route::post('/clear-cart', [CartController::class, 'clearCart'])->withoutMiddleware(['auth:api']);
    });
    Route::prefix('orders')->name('orders.')->middleware(['auth:api'])->group(function () {
        Route::post('/get-order-products', [OrdersController::class, 'getOrdersProducts']);
        Route::post('/get-orders-list', [OrdersController::class, 'getOrdersList']);
        Route::post('/get-order-details', [OrdersController::class, 'getOrderDetails']);
        Route::post('/get-order-tracking-status', [OrdersController::class, 'getOrderTrackingStatus']);
    });
    Route::prefix('user')->name('user.')->group(function () {
        Route::post('/forget-password', [LoginController::class, 'forgetPassword']);
        Route::post('/check-existing-user', [UserController::class, 'checkExistingEmail']);
        Route::post('/change-password', [LoginController::class, 'changePassword']);
        Route::post('/get-user-info', [UserController::class, 'getUserInfo'])->middleware(['auth:api']);
        Route::post('/update-profile', [UserController::class, 'updateProfile'])->middleware(['auth:api']);
        Route::post('/add-shipping-address', [UserController::class, 'addShippingAddress'])->middleware(['auth:api']);
        Route::post('/add-favourites-to-cart', [ProductController::class, 'addFavouritesToCart'])->middleware(['auth:api']);
        Route::post('/get-favourites', [UserController::class, 'getFavourites'])->middleware(['auth:api']);
        Route::post('/resend-otp', [UserController::class, 'resendOtp']);
        Route::post('/get-tickets', [UserController::class, 'getTickets'])->middleware(['auth:api']);
        Route::post('/delete-shipping-address', [UserController::class, 'deleteShippingAddress'])->middleware(['auth:api']);
        Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
        Route::post('/reset-password-verify-otp', [UserController::class, 'resetPasswordVerifyOtp']);
        Route::post('/reset-password', [UserController::class, 'resetPassword']);
        Route::post('/update-default-shipping-address', [UserController::class, 'updateDefaultShippingAddress'])->middleware(['auth:api']);
        Route::post('/update-shipping-address', [UserController::class, 'addShippingAddress'])->middleware(['auth:api']);
        Route::post('/get-shipping-address', [UserController::class, 'getShippingAddress'])->middleware(['auth:api']);
        Route::post('/create-password', [UserController::class, 'createPassword']);
        Route::post('/get-my-tickets', [UserController::class, 'getMyTickets'])->middleware(['auth:api']);
    });
});

Route::get('/data-truncate', function () {
    try {
//        DB::statement('TRUNCATE TABLE campaigns');
//        DB::statement('TRUNCATE TABLE campaign_images');
//        DB::statement('TRUNCATE TABLE product');
//        DB::statement('TRUNCATE TABLE product_attribute');
//        DB::statement('TRUNCATE TABLE product_attribute_change_history');
//        DB::statement('TRUNCATE TABLE product_category');
//        DB::statement('TRUNCATE TABLE product_celebrity_perc');
//        DB::statement('TRUNCATE TABLE product_checkout');
//        DB::statement('TRUNCATE TABLE product_order_details');
//        DB::statement('TRUNCATE TABLE product_order_history');
//        DB::statement('TRUNCATE TABLE product_order_ticket_number');
//        DB::statement('TRUNCATE TABLE product_selected_attributes');
//        DB::statement('TRUNCATE TABLE product_tracking_status');
//        DB::statement('TRUNCATE TABLE product_variations');
        DB::statement('TRUNCATE TABLE faq');

        return return_response(1, 200, 'Data truncated');
    } catch (Exception $exception) {
        dd($exception);
    }
});

Route::get('/db-operation', function () {});