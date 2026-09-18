<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderTrackingController;
use App\Http\Controllers\Frontend\GuestOrderTrackingController;
use App\Http\Controllers\Frontend\CustomerAuthController;
use App\Http\Controllers\Frontend\GoogleAuthController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\FacebookFeedController;
use App\Http\Controllers\Frontend\ChatbotController;
use App\Http\Controllers\Frontend\GeoController;
use App\Http\Controllers\Frontend\PartialController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Backend\PopupController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/partials/{component}', [PartialController::class, 'show'])->name('partials.show');
Route::get('/api/products/more', [HomeController::class, 'loadMore'])->name('api.products.more');
Route::get('/api/quickview/{id}', [ProductController::class, 'quickView'])->name('api.quickview');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/products', [ProductController::class, 'index'])->name('product.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/lp/{slug}', [LandingPageController::class, 'show'])->name('landing.show');
Route::get('/category/{slug}', [ProductController::class, 'category'])->name('category.show');
Route::get('/page/{slug}', [HomeController::class, 'showPage'])->name('page.show');
Route::get('/new-collection', [ProductController::class, 'newCollection'])->name('product.new_collection');
Route::get('/best-sale', [ProductController::class, 'bestSale'])->name('product.best_sale');
Route::get('/flash-deals', [ProductController::class, 'flashDeals'])->name('product.flash_deals');

Route::get('/cart', fn() => redirect()->route('checkout'))->name('cart.index');
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/get', [CartController::class, 'get'])->name('get');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/add-batch', [CartController::class, 'addBatch'])->name('add_batch');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::match(['post', 'delete'], '/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/restore', [CartController::class, 'restore'])->name('restore');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('apply_coupon');
    Route::post('/remove-coupon', [CartController::class, 'removeCoupon'])->name('remove_coupon');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->middleware('throttle:checkout')->name('checkout.process');
Route::post('/checkout/abandoned-lead', [CheckoutController::class, 'saveAbandonedLead'])->name('checkout.abandoned_lead');
Route::get('/geo/divisions', [GeoController::class, 'divisions'])->name('geo.divisions');
Route::get('/geo/districts/{divisionId}', [GeoController::class, 'districts'])->name('geo.districts');
Route::get('/geo/upazilas/{districtId}', [GeoController::class, 'upazilas'])->name('geo.upazilas');
Route::get('/order/success/{orderNumber}', [OrderTrackingController::class, 'success'])->name('order.success');
Route::get('/order/track', [GuestOrderTrackingController::class, 'track'])->name('order.track');
Route::get('/order/history', [GuestOrderTrackingController::class, 'history'])->name('order.history');

Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.post');
Route::match(['get', 'post'], '/logout', [CustomerAuthController::class, 'logout'])->name('logout');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/auth/{provider}', [CustomerAuthController::class, 'redirectToSocial'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [CustomerAuthController::class, 'handleSocialCallback'])->name('social.callback');

Route::prefix('account')->name('customer.')->middleware(['auth'])->group(function () {
    Route::get('/', [CustomerAuthController::class, 'account'])->name('account');
    Route::post('/profile', [CustomerAuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password', [CustomerAuthController::class, 'updatePassword'])->name('password.update');
});

Route::get('/api/quick-view/{id}', [ProductController::class, 'quickView'])->name('api.quick_view');
Route::get('/api/search', [SearchController::class, 'autocomplete'])->name('api.search');
Route::get('/api/recent-sales', [HomeController::class, 'recentSales'])->name('api.recent_sales');
Route::post('/product/{id}/review', [ProductController::class, 'submitReview'])->name('product.review.submit');
Route::post('/api/device/orders', [GuestOrderTrackingController::class, 'apiGetDeviceOrders'])->name('api.device.orders');
Route::post('/api/device/clear-history', [GuestOrderTrackingController::class, 'apiClearDeviceHistory'])->name('api.device.clear_history');
Route::post('/popup-impression/{id}', [PopupController::class, 'trackImpression'])->name('popup.impression');

Route::post('/api/chatbot/message', [ChatbotController::class, 'sendMessage'])->middleware('throttle:30,1')->name('api.chatbot.message');
Route::match(['get', 'post'], '/api/chatbot/history', [ChatbotController::class, 'getHistory'])->name('api.chatbot.history');
Route::post('/api/chatbot/clear', [ChatbotController::class, 'clearHistory'])->name('api.chatbot.clear');
Route::post('/chatbot/quick-order', [ChatbotController::class, 'quickOrder'])->middleware('throttle:10,1')->name('api.chatbot.quick_order');
Route::post('/api/chatbot/quick-order', [ChatbotController::class, 'quickOrder'])->middleware('throttle:10,1')->name('api.chatbot.quick_order_api');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/facebook-product-feed.xml', [FacebookFeedController::class, 'feed'])->name('facebook.feed');
Route::get('/manifest.json', fn() => response()->file(public_path('manifest.json'), ['Content-Type' => 'application/manifest+json']))->name('manifest.json');
Route::get('/sw.js', fn() => response()->file(public_path('sw.js'), ['Content-Type' => 'application/javascript']))->name('sw.js');
