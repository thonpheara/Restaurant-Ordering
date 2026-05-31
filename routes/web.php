<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\layouts\UserwebController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\Login\LogoutController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Categories\CategoryController;
use App\Http\Controllers\Admin\Reservation\ReservationController;
use App\Http\Controllers\Admin\Report\ReportController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\Menu\MenuController;
use App\Http\Controllers\OrderNotificationController;
use App\Http\Controllers\User\UserDashboardController;

// ================================================================================
// PUBLIC ROUTES
// ================================================================================
Route::get('/', [UserwebController::class, 'pages'])->name('userweb.index');
Route::post('/order/store', [MenuController::class, 'storeOrder'])->name('order.store');

Route::get('/login',   [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// ================================================================================
// PROTECTED ROUTES — all logged-in users
// ================================================================================

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboards', function () {
        if (auth()->user()->hasAdminAccess()) {
            return app(DashboardController::class)->pageDashboard();
        } else {
            return app(UserDashboardController::class)->pageDashboard();
        }
    })->name('dashboard.index');

    Route::middleware(['admin'])->group(function () {
        Route::get('/admin-dashboard', [DashboardController::class, 'pageDashboard'])->name('admin.dashboard');
    });

    // Products
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products-list',           'index')  ->name('allproduct.index');
        Route::get('/products-form',           'create') ->name('products-form.index');
        Route::post('/products-form/store',    'store')  ->name('products-form.store');
        Route::get('/products/edit/{id}',      'edit')   ->name('product.edit');
        Route::put('/products/update/{id}',    'update') ->name('product.update');
        Route::delete('/products/delete/{id}', 'destroy')->name('product.destroy');
        Route::get('/products/{id}',           'show')   ->name('product.show');
        Route::get('/products/{id}',           'pheara')   ->name('product.pheara');
    });

    // Categories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/allcategories',             'pageAllcategory')->name('allcategory.index');
        Route::get('/addcategories',             'pageAddcategory')->name('addcategory.index');
        Route::post('/addcategories/store',      'store')          ->name('addcategory.store');
        Route::get('/categories/edit/{id}',      'edit')           ->name('category.edit');
        Route::put('/categories/update/{id}',    'update')         ->name('category.update');
        Route::delete('/categories/delete/{id}', 'destroy')        ->name('category.destroy');
    });

    // Menu & Orders
    Route::get('/menu',               [MenuController::class, 'pageMenu'])    ->name('menu.index');
    Route::get('/notifications/orders', [OrderNotificationController::class, 'index'])->name('notifications.orders');
    Route::post('/order/{id}/status', [MenuController::class, 'updateStatus'])->name('order.status');
    Route::delete('/order/{id}',      [MenuController::class, 'destroyOrder'])->name('order.destroy');

    // User Dashboard
    Route::get('/user-dashboard', [UserDashboardController::class, 'pageDashboard'])->name('user.dashboard');

    // Reservations
    Route::controller(ReservationController::class)->group(function () {
        Route::get('/reservations',        'index')  ->name('reservations.index');
        Route::get('/reservations/create', 'create') ->name('reservations.create');
        Route::post('/reservations',       'store')  ->name('reservations.store');
        Route::delete('/reservations/{id}','destroy')->name('reservations.destroy');
    });

    Route::prefix('admin')->group(function () {
        Route::get('reservations/search', [ReservationController::class, 'search'])->name('reservations.search');
        Route::resource('reservations', ReservationController::class)
            ->except(['index', 'create', 'store', 'destroy']);
    });

    // Reports
    Route::get('/reports',              [ReportController::class, 'pageReport']) ->name('report.index');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('report.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPDF'])->name('report.pdf');

    // Settings
    Route::get('/settings',        [SettingController::class, 'pageSetting'])->name('setting.index');
    Route::post('/settings/save',  [SettingController::class, 'save'])       ->name('setting.save');
    Route::post('/settings/image', [SettingController::class, 'deleteImage'])->name('setting.deleteImage');

    Route::post('/setting/dish',               [SettingController::class, 'storeDish'])   ->name('setting.dish.store');
    Route::post('/setting/dish/{id}/update',   [SettingController::class, 'updateDish'])  ->name('setting.dish.update');
    Route::post('/setting/dish/{id}/delete',   [SettingController::class, 'destroyDish']) ->name('setting.dish.destroy');
    Route::post('/setting/ticker',             [SettingController::class, 'storeTicker']) ->name('setting.ticker.store');
    Route::post('/setting/ticker/{id}/delete', [SettingController::class, 'destroyTicker'])->name('setting.ticker.destroy');
    Route::post('/setting/feature',            [SettingController::class, 'saveFeature']) ->name('setting.feature.save');

    // Deletion Requests
    Route::get('/deletion-requests',                      [SettingController::class, 'deletionRequests'])->name('deletion-requests.index');
    Route::post('/deletion-requests/{id}/approve',        [SettingController::class, 'approveDeletion']) ->name('deletion-requests.approve');
    Route::post('/deletion-requests/{id}/deny',           [SettingController::class, 'denyDeletion'])    ->name('deletion-requests.deny');
    Route::delete('/deletion-requests/delete-all-approved', [SettingController::class, 'deleteAllApproved'])->name('deletion-requests.delete-all-approved');
    Route::post('/resource-deletion-requests/{id}/approve', [SettingController::class, 'approveResourceDeletion'])->name('resource-deletion-requests.approve');
    Route::post('/resource-deletion-requests/{id}/deny', [SettingController::class, 'denyResourceDeletion'])->name('resource-deletion-requests.deny');
    Route::delete('/resource-deletion-requests/delete-all-approved', [SettingController::class, 'deleteAllApprovedResourceRequests'])->name('resource-deletion-requests.delete-all-approved');

    // Profile
    Route::post('/settings/profile/info',     [ProfileController::class, 'updateInfo'])    ->name('profile.updateInfo');
    Route::post('/settings/profile/avatar',   [ProfileController::class, 'updateAvatar'])  ->name('profile.updateAvatar');
    Route::post('/settings/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::delete('/settings/profile',        [ProfileController::class, 'destroy'])       ->name('profile.destroy');

    // ============================================================================
    // ADMIN-ONLY ROUTES
    // ============================================================================

    Route::middleware(['admin'])->group(function () {
        Route::get('/users',           [UserController::class, 'index'])  ->name('user.index');
        Route::get('/users/create',    [UserController::class, 'create']) ->name('user.create');
        Route::post('/users',          [UserController::class, 'store'])  ->name('user.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])   ->name('user.edit');
        Route::put('/users/{id}',      [UserController::class, 'update']) ->name('user.update');
        Route::delete('/users/{id}',   [UserController::class, 'destroy'])->name('user.destroy');
    });

});