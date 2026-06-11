<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PharmaRegisterController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SearchHistoryController;
use App\Http\Controllers\Api\StatController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\SupplierNotificationController;
use App\Http\Controllers\Api\UserRegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== PUBLIC ROUTES ====================

// User Registration
Route::post('/register/user', [UserRegisterController::class, 'register']);
Route::post('/register/pharmacy', [PharmaRegisterController::class, 'register']);

// Email Verification
Route::post('/verify-otp', [UserRegisterController::class, 'verifyOtp']);
Route::post('/resend-otp', [UserRegisterController::class, 'resendOtp']);

// Password Management
Route::post('/forgot-password', [UserRegisterController::class, 'forgotPassword']);
Route::post('/reset-password', [UserRegisterController::class, 'resetPassword']);

// Authentication
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

// ==================== ADMIN ROUTES ====================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Pharmacy Management
    Route::get('/pharmacies/pending', [AdminController::class, 'pendingPharmacies']);
    Route::get('/pharmacies/approved', [AdminController::class, 'approvedPharmacies']);
    Route::get('/pharmacies/{pharmacy}', [AdminController::class, 'show']);
    Route::put('/pharmacies/{pharmacy}/approve', [AdminController::class, 'approve']);
    Route::delete('/pharmacies/{pharmacy}/reject', [AdminController::class, 'reject']);
    Route::get('/pharmacies/{id}/inventory', [AdminController::class, 'pharmacyInventory']);
    
    // Medicine Management
    Route::post('/medicines/upload', [AdminController::class, 'uploadMedicines']);
    
    // Dashboard Statistics
    Route::get('/dashboard/stats', [AdminController::class, 'dashboardStats']);
    Route::get('/dashboard/top-pharmacies', [AdminController::class, 'topPharmacies']);
    Route::get('/dashboard/sales-trend', [AdminController::class, 'salesTrend']);
});

// ==================== PHARMACY ROUTES ====================

Route::middleware(['auth:sanctum', 'pharmacy.approved'])->group(function () {
    // Inventory Management
    Route::post('/inventory/upload', [InventoryController::class, 'upload']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    
    // Sales Management
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales', [SaleController::class, 'index']);
    
    // Low Stock Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    
    // Order Offer Notifications
    Route::get('/notifications/offers', [NotificationController::class, 'orderOfferNotifications']);
    Route::put('/notifications/offers/{id}/read', [NotificationController::class, 'markOrderOfferRead']);
    Route::put('/notifications/offers/read-all', [NotificationController::class, 'markAllOrderOffersRead']);
    
    // Statistics
    Route::get('/pharmacy/stats', [StatController::class, 'dashboard']);
    Route::get('/pharmacy/top-medicines', [StatController::class, 'topMedicines']);
    Route::get('/pharmacy/sales-chart', [StatController::class, 'salesChart']);
    Route::get('/pharmacy/low-stock', [StatController::class, 'lowStock']);
    
    // Order Management
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'orderDetails']);
    Route::get('/orders/{id}/offers', [OrderController::class, 'orderOffers']);
    Route::put('/orders/offers/{offerId}/accept', [OrderController::class, 'acceptOffer']);
    Route::put('/orders/offers/{offerId}/reject', [OrderController::class, 'rejectOffer']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
});

// ==================== SUPPLIER ROUTES ====================

Route::middleware(['auth:sanctum'])->group(function () {
    // Order Management
    Route::post('/supplier/orders/pending', [SupplierController::class, 'pendingOrders']);
    Route::post('/supplier/orders/{orderId}/offer', [SupplierController::class, 'makeOffer']);
    Route::get('/supplier/offers', [SupplierController::class, 'myOffers']);
    Route::put('/supplier/offers/{offerId}/cancel', [SupplierController::class, 'cancelOffer']);
    
    // Notifications
    Route::get('/supplier/notifications', [SupplierNotificationController::class, 'index']);
    Route::get('/supplier/notifications/rejected', [SupplierNotificationController::class, 'rejectedNotifications']);
    Route::put('/supplier/notifications/{id}/read', [SupplierNotificationController::class, 'markAsRead']);
    Route::put('/supplier/notifications/read-all', [SupplierNotificationController::class, 'markAllAsRead']);
    Route::delete('/supplier/notifications/{id}', [SupplierNotificationController::class, 'destroy']);
    Route::delete('/supplier/notifications', [SupplierNotificationController::class, 'clearAll']);
});

// ==================== CUSTOMER ROUTES ====================

Route::middleware(['auth:sanctum'])->group(function () {
    // Favorites
    Route::post('/favorites/pharmacies/{id}', [FavoriteController::class, 'addPharmacy']);
    Route::delete('/favorites/pharmacies/{id}', [FavoriteController::class, 'removePharmacy']);
    Route::get('/favorites/pharmacies', [FavoriteController::class, 'getFavoritePharmacies']);
    Route::post('/favorites/medicines/{id}', [FavoriteController::class, 'addMedicine']);
    Route::delete('/favorites/medicines/{id}', [FavoriteController::class, 'removeMedicine']);
    Route::get('/favorites/medicines', [FavoriteController::class, 'getFavoriteMedicines']);
    
    // Reviews
    Route::post('/pharmacies/{id}/rate', [ReviewController::class, 'rate']);
    Route::get('/pharmacies/{id}/reviews', [ReviewController::class, 'pharmacyReviews']);
    
    // Search
    Route::get('/search/medicines', [SearchController::class, 'searchMedicines']);
    Route::post('/search/pharmacies', [SearchController::class, 'findPharmacies']);
    
    // Search History
    Route::get('/search/history', [SearchHistoryController::class, 'index']);
    Route::delete('/search/history/{id}', [SearchHistoryController::class, 'destroy']);
    Route::delete('/search/history', [SearchHistoryController::class, 'clearAll']);
});