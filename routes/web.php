<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\AuthController as CustomerAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SiteInfoController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\StorySectionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue — Shopify-style URLs.
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/all', [CollectionController::class, 'all'])->name('products.index');
Route::get('/collections/{category:slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'throttle:reviews'])->name('reviews.store');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:search')->name('search.suggest');

// Content pages and policies.
Route::get('/pages/our-story', [PageController::class, 'story'])->name('story');
Route::get('/pages/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/pages/size-guide', [PageController::class, 'sizeGuide'])->name('size-guide');
Route::get('/pages/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/pages/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::get('/policies/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/policies/terms-of-service', [PageController::class, 'terms'])->name('terms');
Route::get('/policies/refund-policy', [PageController::class, 'returns'])->name('returns');
Route::get('/policies/shipping-policy', [PageController::class, 'shipping'])->name('shipping');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:newsletter')->name('newsletter.store');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/drawer', [CartController::class, 'drawer'])->name('cart.drawer');
Route::post('/cart/add', [CartController::class, 'add'])->middleware('throttle:cart')->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->middleware('throttle:cart')->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->middleware('throttle:cart')->name('cart.remove');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->middleware('throttle:codes')->name('cart.coupon.apply');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->middleware('throttle:cart')->name('cart.coupon.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->middleware('throttle:cart')->name('wishlist.toggle');
Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->middleware('throttle:cart')->name('wishlist.remove');

Route::middleware('guest')->group(function () {
    Route::get('/account/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/account/login', [CustomerAuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/account/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/account/register', [CustomerAuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::post('/account/logout', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
    Route::post('/account/settings', [AccountController::class, 'updateSettings'])->middleware('throttle:account')->name('account.settings.update');
});

// Guest checkout is allowed, like Shopify; signed-in customers get their
// details pre-filled and the order saved to their account.
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('/checkout/{order:uid}/thank-you', [CheckoutController::class, 'thankYou'])->name('checkout.thank-you');
Route::post('/checkout/validate-agent-reference', [CheckoutController::class, 'validateAgentReference'])->middleware('throttle:codes')->name('checkout.validate-agent');

Route::get('/track-order', [OrderTrackingController::class, 'index'])->middleware('throttle:track-order')->name('order.track');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
| Login lives at /admin/login under the name "auth.login" (matching the
| admin theme's Blade views/links); everything else sits behind the
| "admin.auth" guard middleware.
*/
Route::prefix('admin')->group(function () {
    Route::middleware(['guest:admin', 'throttle:admin-login'])->match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('admin.auth')->name('admin.')->group(function () {
        Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::post('clear-cache', [MaintenanceController::class, 'clearCache'])->name('clear-cache');
        Route::post('sitemap', [SitemapController::class, 'generate'])->name('sitemap.generate');

        // Products
        Route::get('products', [AdminProductController::class, 'products'])->name('products');
        Route::get('products/all', [AdminProductController::class, 'allProduct'])->name('all-product');
        Route::match(['get', 'post'], 'products/form', [AdminProductController::class, 'addProduct'])->name('add-product');
        Route::get('products/view', [AdminProductController::class, 'viewProduct'])->name('view-product');
        Route::post('products/status', [AdminProductController::class, 'updateStatus'])->name('products.status');
        Route::post('products/approve', [AdminProductController::class, 'updateApprove'])->name('update-approve');
        Route::post('products/delete', [AdminProductController::class, 'deleteProduct'])->name('delete-product');

        // Attributes
        Route::get('attributes', [AdminProductController::class, 'attributes'])->name('attributes');
        Route::get('attributes/all', [AdminProductController::class, 'allAttributes'])->name('all-attributes');
        Route::match(['get', 'post'], 'attributes/form', [AdminProductController::class, 'addAttribute'])->name('add-attribute');
        Route::post('attributes/delete', [AdminProductController::class, 'deleteAttribute'])->name('attribute.delete');

        // Categories
        Route::get('categories', [CategoryController::class, 'category'])->name('category');
        Route::get('categories/all', [CategoryController::class, 'allCategories'])->name('all-categories');
        Route::match(['get', 'post'], 'categories/form', [CategoryController::class, 'addCategory'])->name('add-category');
        Route::post('categories/status', [CategoryController::class, 'updateStatus'])->name('category.status');
        Route::post('categories/delete', [CategoryController::class, 'deleteCategory'])->name('category.delete');

        // Banners (homepage hero carousel)
        Route::get('banners', [BannerController::class, 'banners'])->name('banners');
        Route::get('banners/all', [BannerController::class, 'allBanners'])->name('all-banners');
        Route::match(['get', 'post'], 'banners/form', [BannerController::class, 'addBanner'])->name('add-banner');
        Route::post('banners/status', [BannerController::class, 'updateStatus'])->name('banners.status');
        Route::post('banners/delete', [BannerController::class, 'deleteBanner'])->name('banners.delete');

        // Our Story page sections (alternating image / text blocks)
        Route::match(['get', 'post'], 'story-sections/intro', [StorySectionController::class, 'intro'])->name('story-intro');
        Route::get('story-sections', [StorySectionController::class, 'sections'])->name('story-sections');
        Route::get('story-sections/all', [StorySectionController::class, 'allSections'])->name('all-story-sections');
        Route::match(['get', 'post'], 'story-sections/form', [StorySectionController::class, 'addSection'])->name('add-story-section');
        Route::post('story-sections/status', [StorySectionController::class, 'updateStatus'])->name('story-sections.status');
        Route::post('story-sections/delete', [StorySectionController::class, 'deleteSection'])->name('story-sections.delete');

        // Orders
        Route::get('orders', [OrderController::class, 'orders'])->name('orders');
        Route::get('orders/all', [OrderController::class, 'allOrder'])->name('all-order');
        Route::match(['get', 'post'], 'orders/create', [OrderController::class, 'createOrder'])->name('create-order');
        Route::get('orders/view',[OrderController::class, 'viewOrder'])->name('view-order');
        Route::post('orders/status', [OrderController::class, 'updateOrderStatus'])->name('order.update-status');
        Route::post('orders/tracking', [OrderController::class, 'addTracking'])->name('order.add-tracking');
        Route::post('orders/proof', [OrderController::class, 'addProof'])->name('order.add-proof');

        // Reviews (moderation + store replies)
        Route::get('reviews', [AdminReviewController::class, 'reviews'])->name('reviews');
        Route::post('reviews/visibility', [AdminReviewController::class, 'updateVisibility'])->name('reviews.visibility');
        Route::post('reviews/reply', [AdminReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('reviews/{id}', [AdminReviewController::class, 'deleteReview'])->name('reviews.delete');

        // Users
        Route::get('users', [UserController::class, 'users'])->name('users');
        Route::get('users/all', [UserController::class, 'allUser'])->name('all-user');
        Route::get('users/view', [UserController::class, 'viewUser'])->name('view-user');

        // Site info, FAQs, contact
        Route::match(['get', 'post'], 'site-info', [SiteInfoController::class, 'siteInfo'])->name('siteinfo');
        Route::delete('site-info/image', [SiteInfoController::class, 'deleteSiteInfoImage'])->name('siteinfo.image.delete');
        Route::post('site-info/gallery', [SiteInfoController::class, 'storeGalleryImage'])->name('siteinfo.gallery.store');
        Route::put('site-info/gallery/{galleryImage}', [SiteInfoController::class, 'updateGalleryImage'])->name('siteinfo.gallery.update');
        Route::delete('site-info/gallery/{galleryImage}', [SiteInfoController::class, 'deleteGalleryImage'])->name('siteinfo.gallery.delete');
        Route::get('faqs', [SiteInfoController::class, 'faq'])->name('faq');
        Route::get('faqs/all', [SiteInfoController::class, 'allFaqs'])->name('all-faqs');
        Route::match(['get', 'post'], 'faqs/form', [SiteInfoController::class, 'addFaq'])->name('add-faq');
        Route::post('faqs/delete', [SiteInfoController::class, 'deleteFaq'])->name('faq.delete');
        Route::get('contact', [SiteInfoController::class, 'contact'])->name('contact');
        Route::post('contact/delete', [SiteInfoController::class, 'deleteContact'])->name('contact.delete');

        // Coupons
        Route::get('coupons', [CouponController::class, 'coupons'])->name('coupons');
        Route::match(['get', 'post'], 'coupons/form', [CouponController::class, 'addCoupon'])->name('add-coupon');
        Route::post('coupons/status', [CouponController::class, 'updateStatus'])->name('coupons.status');
        Route::delete('coupons/{id}', [CouponController::class, 'deleteCoupon'])->name('coupons.delete');

        // Agents (vendors / referrers) + their sales & commission log
        Route::get('agents', [AgentController::class, 'agents'])->name('agents');
        Route::match(['get', 'post'], 'agents/form', [AgentController::class, 'addAgent'])->name('add-agent');
        Route::delete('agents/{id}', [AgentController::class, 'deleteAgent'])->name('agents.delete');

        Route::get('agent-sales', [AgentController::class, 'agentSales'])->name('agent-sales');
        Route::match(['get', 'post'], 'agent-sales/form', [AgentController::class, 'addAgentSale'])->name('add-agent-sale');
        Route::delete('agent-sales/{id}', [AgentController::class, 'deleteAgentSale'])->name('agent-sales.delete');
    });
});

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->middleware('admin.auth')
    ->name('auth.logout');
