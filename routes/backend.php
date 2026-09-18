<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\LogisticsController;
use App\Http\Controllers\Backend\RefundController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\AuditLogController;
use App\Http\Controllers\Backend\CustomerCrmController;
use App\Http\Controllers\Backend\AbandonedCartController;
use App\Http\Controllers\Backend\FraudController;
use App\Http\Controllers\Backend\PromotionController;
use App\Http\Controllers\Backend\SupportController;
use App\Http\Controllers\Backend\RbacController;
use App\Http\Controllers\Backend\GlobalSettingsController;
use App\Http\Controllers\Backend\InventoryController;
use App\Http\Controllers\Backend\SmsSettingController;
use App\Http\Controllers\Backend\CmsReviewController;
use App\Http\Controllers\Backend\SocialSettingController;
use App\Http\Controllers\Backend\NotificationCommerceController;
use App\Http\Controllers\Backend\ThemeSettingController;
use App\Http\Controllers\Backend\AiIntegrationController;
use App\Http\Controllers\Backend\SystemController;
use App\Http\Controllers\Backend\ProductDemandController;
use App\Http\Controllers\Backend\MetaSettingController;
use App\Http\Controllers\Backend\OrderBumpController;
use App\Http\Controllers\Backend\BlocklistController;
use App\Http\Controllers\Backend\PopupController;

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::match(['get', 'post'], '/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::get('/admin/forgot-password', [AuthController::class, 'showForgotPassword'])->name('admin.forgot_password');
Route::post('/admin/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->middleware('throttle:3,1')->name('admin.forgot_password.send_otp');
Route::post('/admin/forgot-password/reset', [AuthController::class, 'verifyOtpAndReset'])->name('admin.forgot_password.reset');
Route::post('/admin/forgot-password/verify-otp-ajax', [AuthController::class, 'verifyOtpAjax'])->name('admin.forgot_password.verify_otp_ajax');

Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'admin.permission'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [AdminController::class, 'orderShow'])->name('orders.show');
    Route::get('/orders/{id}/edit-items', [AdminController::class, 'orderEditItems'])->name('orders.edit_items');
    Route::post('/orders/{id}/update-items', [AdminController::class, 'orderUpdateItems'])->name('orders.update_items');
    Route::get('/orders/search/products', [AdminController::class, 'searchProductsAjax'])->name('orders.search_products');
    Route::get('/orders/{id}/ajax-details', [AdminController::class, 'orderAjaxDetails'])->name('orders.ajax_details');
    Route::post('/orders/{id}/ajax-update-full', [AdminController::class, 'orderAjaxUpdateFull'])->name('orders.ajax_update_full');
    Route::post('/orders/bulk-call-status', [AdminController::class, 'orderAjaxBulkCallStatus'])->name('orders.bulk_call_status');
    Route::post('/orders/bulk-status', [AdminController::class, 'orderAjaxBulkStatus'])->name('orders.bulk_status');
    Route::post('/orders/{id}/status', [AdminController::class, 'orderUpdateStatus'])->name('orders.status');
    Route::post('/orders/{id}/ajax-status', [AdminController::class, 'orderAjaxStatus'])->name('orders.ajax_status');
    Route::post('/orders/{id}/ajax-call-status', [AdminController::class, 'orderAjaxCallStatus'])->name('orders.ajax_call_status');
    Route::post('/orders/unlink-device/{deviceToken}', [AdminController::class, 'unlinkDeviceSession'])->name('orders.unlink_device');
    Route::get('/orders/{id}/print', [AdminController::class, 'orderPrint'])->name('orders.print');
    Route::get('/orders/{id}/invoice', [AdminController::class, 'orderPrint'])->name('orders.invoice');
    Route::get('/orders/{id}/packing-slip', [AdminController::class, 'orderPackingSlip'])->name('orders.packing_slip');
    Route::delete('/orders/{id}', [AdminController::class, 'orderDelete'])->name('orders.delete');
    Route::delete('/orders/{id}/ajax-delete', [AdminController::class, 'orderAjaxDelete'])->name('orders.ajax_delete');

    Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
    Route::post('/logistics/dispatch/{orderId}', [LogisticsController::class, 'dispatch'])->name('logistics.dispatch');
    Route::post('/logistics/bulk-dispatch', [LogisticsController::class, 'bulkDispatch'])->name('logistics.bulk_dispatch');
    Route::get('/logistics/track/{trackingCode}', [LogisticsController::class, 'track'])->name('logistics.track');

    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/create/{orderId}', [RefundController::class, 'store'])->name('refunds.store');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::get('/products/export-csv', [ProductController::class, 'exportCsv'])->name('products.export_csv');
    Route::post('/products/import-csv', [ProductController::class, 'importCsv'])->name('products.import_csv');
    Route::get('/products/{id}/json', [ProductController::class, 'json'])->name('products.json');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/ajax-save', [ProductController::class, 'ajaxSave'])->name('products.ajax_save');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('/products/{id}/update', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{id}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::delete('/products/{id}', [ProductController::class, 'delete'])->name('products.delete');
    Route::delete('/products/{id}/ajax-delete', [ProductController::class, 'ajaxDelete'])->name('products.ajax_delete');
    Route::get('/products/ai-generator', [ProductController::class, 'aiGenerator'])->name('products.ai');
    Route::post('/products/ai-generator/generate', [ProductController::class, 'aiGenerateDetails'])->name('products.ai.generate');
    Route::post('/products/ai-generator/save', [ProductController::class, 'aiSaveProduct'])->name('products.ai.save');
    Route::post('/products/ai-generator/category/create', [ProductController::class, 'aiCreateCategory'])->name('products.ai.category.create');
    Route::post('/products/ai-seo-description', [ProductController::class, 'aiGenerateSeoDescription'])->name('products.ai.seo_description');
    Route::get('/product-demands', [ProductDemandController::class, 'index'])->name('product_demands.index');
    Route::post('/product-demands/{id}/status', [ProductDemandController::class, 'updateStatus'])->name('product_demands.status');
    Route::post('/product-demands/{id}/notes', [ProductDemandController::class, 'updateNotes'])->name('product_demands.notes');
    Route::delete('/product-demands/{id}', [ProductDemandController::class, 'destroy'])->name('product_demands.destroy');

    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::get('/categories/tree/json', [AdminController::class, 'categoriesTreeJson'])->name('categories.tree_json');
    Route::get('/categories/{id}/json', [AdminController::class, 'categoryJson'])->name('categories.json');
    Route::get('/categories/{id}/delete-check', [AdminController::class, 'categoryDeleteCheck'])->name('categories.delete_check');
    Route::post('/categories/store', [AdminController::class, 'categoryStore'])->name('categories.store');
    Route::post('/categories/ajax-save', [AdminController::class, 'categoryAjaxSave'])->name('categories.ajax_save');
    Route::post('/categories/{id}/update', [AdminController::class, 'categoryUpdate'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'categoryDelete'])->name('categories.delete');
    Route::delete('/categories/{id}/ajax-delete', [AdminController::class, 'categoryAjaxDelete'])->name('categories.ajax_delete');

    Route::get('/banners', [AdminController::class, 'banners'])->name('banners.index');
    Route::get('/banners/{id}/json', [AdminController::class, 'bannerJson'])->name('banners.json');
    Route::post('/banners/store', [AdminController::class, 'bannerStore'])->name('banners.store');
    Route::post('/banners/ajax-save', [AdminController::class, 'bannerAjaxSave'])->name('banners.ajax_save');
    Route::post('/banners/{id}/update', [AdminController::class, 'bannerUpdate'])->name('banners.update');
    Route::delete('/banners/{id}', [AdminController::class, 'bannerDelete'])->name('banners.delete');
    Route::delete('/banners/{id}/ajax-delete', [AdminController::class, 'bannerAjaxDelete'])->name('banners.ajax_delete');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    Route::get('/customers', [CustomerCrmController::class, 'index'])->name('customers.index');
    Route::get('/customers/{phone}', [CustomerCrmController::class, 'show'])->name('crm.show');
    Route::post('/customers/{phone}/wallet', [CustomerCrmController::class, 'adjustWallet'])->name('crm.wallet');

    Route::get('/abandoned-carts', [AbandonedCartController::class, 'index'])->name('abandoned.index');
    Route::post('/abandoned-carts/{id}/recover', [AbandonedCartController::class, 'markRecovered'])->name('abandoned.recover');
    Route::get('/abandoned-carts/{id}/edit-items', [AbandonedCartController::class, 'editCartItems'])->name('abandoned.edit_items');
    Route::post('/abandoned-carts/{id}/update-items', [AbandonedCartController::class, 'updateCartItems'])->name('abandoned.update_items');

    Route::get('/fraud-checker', [FraudController::class, 'index'])->name('fraud.index');
    Route::post('/fraud-checker/evaluate/{orderId}', [FraudController::class, 'evaluate'])->name('fraud.evaluate');
    Route::post('/fraud-checker/status/{orderId}', [FraudController::class, 'updateStatus'])->name('fraud.status');

    Route::get('/coupons', [PromotionController::class, 'coupons'])->name('coupons.index');
    Route::post('/coupons/save', [PromotionController::class, 'couponSave'])->name('coupons.save');
    Route::delete('/coupons/{id}', [PromotionController::class, 'couponDelete'])->name('coupons.delete');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{id}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');

    Route::get('/rbac', [RbacController::class, 'index'])->name('rbac.index');
    Route::post('/rbac/sync', [RbacController::class, 'syncPermissions'])->name('rbac.sync');
    Route::post('/rbac/roles', [RbacController::class, 'storeRole'])->name('rbac.roles.store');
    Route::delete('/rbac/roles/{roleId}', [RbacController::class, 'deleteRole'])->name('rbac.roles.delete');
    Route::post('/rbac/roles/{roleId}/permissions', [RbacController::class, 'updateRolePermissions'])->name('rbac.permissions');
    Route::post('/rbac/users', [RbacController::class, 'storeUser'])->name('rbac.users.store');
    Route::get('/rbac/users/{userId}/json', [RbacController::class, 'getUserJson'])->name('rbac.users.json');
    Route::post('/rbac/users/{userId}/update', [RbacController::class, 'updateUser'])->name('rbac.users.update');
    Route::delete('/rbac/users/{userId}', [RbacController::class, 'deleteUser'])->name('rbac.users.delete');
    Route::post('/rbac/users/{userId}/role', [RbacController::class, 'assignUserRole'])->name('rbac.user_role');

    Route::get('/rbac/users/{userId}/permissions', [RbacController::class, 'userPermissions'])->name('rbac.user_permissions');
    Route::post('/rbac/users/{userId}/permissions', [RbacController::class, 'syncUserPermissions'])->name('rbac.user_permissions.sync');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    Route::post('/settings/update', [AdminController::class, 'settingsUpdate'])->name('settings.update');
    Route::post('/settings/ajax-update', [AdminController::class, 'settingsAjaxUpdate'])->name('settings.ajax_update');
    Route::get('/enterprise-settings', [GlobalSettingsController::class, 'index'])->name('settings.enterprise');
    Route::get('/settings/couriers', [GlobalSettingsController::class, 'couriers'])->name('settings.couriers');
    Route::get('/settings/payments', [GlobalSettingsController::class, 'payments'])->name('settings.payments');
    Route::get('/settings/fraud-engine', [GlobalSettingsController::class, 'fraudEngine'])->name('settings.fraud');
    Route::get('/settings/gtm', [GlobalSettingsController::class, 'gtm'])->name('settings.gtm');
    Route::get('/settings/meta', [MetaSettingController::class, 'index'])->name('settings.meta');
    Route::post('/settings/meta', [MetaSettingController::class, 'update'])->name('settings.meta.update');
    Route::get('/settings/smtp', [GlobalSettingsController::class, 'smtp'])->name('settings.smtp');
    Route::post('/settings/smtp/test', [GlobalSettingsController::class, 'testSmtp'])->name('settings.smtp.test');
    Route::post('/enterprise-settings/update', [GlobalSettingsController::class, 'update'])->name('settings.enterprise.update');
    Route::post('/enterprise-settings/backup', [GlobalSettingsController::class, 'triggerBackup'])->name('settings.backup');

    Route::post('/media/upload', [AdminController::class, 'mediaUpload'])->name('media.upload');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/suppliers', [InventoryController::class, 'storeSupplier'])->name('inventory.suppliers.store');
    Route::delete('/inventory/suppliers/{id}', [InventoryController::class, 'deleteSupplier'])->name('inventory.suppliers.delete');
    Route::post('/inventory/purchases', [InventoryController::class, 'storePurchase'])->name('inventory.purchases.store');

    Route::get('/sms-settings', [SmsSettingController::class, 'index'])->name('sms.index');
    Route::post('/sms-settings/update', [SmsSettingController::class, 'update'])->name('sms.update');
    Route::post('/sms-settings/test', [SmsSettingController::class, 'sendTest'])->name('sms.test');

    Route::get('/pages', [CmsReviewController::class, 'pages'])->name('pages.index');
    Route::post('/pages/store', [CmsReviewController::class, 'storePage'])->name('pages.store');
    Route::post('/pages/{id}/update', [CmsReviewController::class, 'updatePage'])->name('pages.update');
    Route::delete('/pages/{id}', [CmsReviewController::class, 'deletePage'])->name('pages.delete');

    Route::get('/reviews', [CmsReviewController::class, 'reviews'])->name('reviews.index');
    Route::post('/reviews/{id}/toggle', [CmsReviewController::class, 'toggleReviewStatus'])->name('reviews.toggle');
    Route::delete('/reviews/{id}', [CmsReviewController::class, 'deleteReview'])->name('reviews.delete');

    Route::get('/social-settings', [SocialSettingController::class, 'index'])->name('social_settings.index');
    Route::post('/social-settings/update', [SocialSettingController::class, 'update'])->name('social_settings.update');

    Route::get('/notification-settings', [NotificationCommerceController::class, 'index'])->name('notification_settings.index');
    Route::post('/notification-settings/update', [NotificationCommerceController::class, 'update'])->name('notification_settings.update');

    Route::get('/theme-settings', [ThemeSettingController::class, 'index'])->name('theme_settings.index');
    Route::post('/theme-settings/update', [ThemeSettingController::class, 'update'])->name('theme_settings.update');
    Route::get('/settings/theme', [ThemeSettingController::class, 'index'])->name('settings.theme');
    Route::post('/settings/theme', [ThemeSettingController::class, 'update'])->name('settings.theme.update');

    Route::get('/settings/ai', [AiIntegrationController::class, 'index'])->name('settings.ai');
    Route::post('/settings/ai', [AiIntegrationController::class, 'update'])->name('settings.ai.update');
    Route::post('/settings/ai/test', [AiIntegrationController::class, 'testConnection'])->name('settings.ai.test');
    Route::post('/settings/ai/sync-models', [AiIntegrationController::class, 'syncModels'])->name('settings.ai.sync_models');
    Route::post('/settings/ai/playground/product', [AiIntegrationController::class, 'playgroundProduct'])->name('settings.ai.playground.product');
    Route::get('/bumps', [OrderBumpController::class, 'index'])->name('bumps.index');
    Route::post('/bumps/store', [OrderBumpController::class, 'store'])->name('bumps.store');
    Route::post('/bumps/{id}/toggle', [OrderBumpController::class, 'toggle'])->name('bumps.toggle');
    Route::delete('/bumps/{id}', [OrderBumpController::class, 'destroy'])->name('bumps.destroy');

    Route::get('/blocklist', [BlocklistController::class, 'index'])->name('blocklist.index');
    Route::post('/blocklist/store', [BlocklistController::class, 'store'])->name('blocklist.store');
    Route::post('/blocklist/{id}/toggle', [BlocklistController::class, 'toggle'])->name('blocklist.toggle');
    Route::delete('/blocklist/{id}', [BlocklistController::class, 'destroy'])->name('blocklist.destroy');

    Route::get('/popups', [PopupController::class, 'index'])->name('popups.index');
    Route::post('/popups/store', [PopupController::class, 'store'])->name('popups.store');
    Route::get('/popups/{id}/json', [PopupController::class, 'json'])->name('popups.json');
    Route::post('/popups/{id}/update', [PopupController::class, 'update'])->name('popups.update');
    Route::post('/popups/{id}/toggle', [PopupController::class, 'toggleStatus'])->name('popups.toggle');
    Route::delete('/popups/{id}', [PopupController::class, 'destroy'])->name('popups.destroy');

    Route::get('/system/info', [SystemController::class, 'info'])->name('system.info');
    Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->name('system.clear_cache');
});

Route::post('/api/courier/webhook/{provider}', [LogisticsController::class, 'webhook'])->name('api.courier.webhook');
