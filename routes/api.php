<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PharmaRegisterController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatController;
use App\Http\Controllers\Api\UserRegisterController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Registration (customer & supplier)
Route::post('/register/user', [UserRegisterController::class, 'register']);
Route::post('/verify-otp', [UserRegisterController::class, 'verifyOtp']);

// Pharmacy Registration
Route::post('/register/pharmacy', [PharmaRegisterController::class, 'register']);

// User Login & Logout
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

// Resend OTP
Route::post('/resend-otp', [UserRegisterController::class, 'resendOtp']);

// Forgot/Reset Password
Route::post('/forgot-password', [UserRegisterController::class, 'forgotPassword']);
Route::post('/reset-password', [UserRegisterController::class, 'resetPassword']);

// Admin routes (protected)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/pharmacies/pending', [AdminController::class, 'pendingPharmacies']);
    Route::get('/pharmacies/approved', [AdminController::class, 'approvedPharmacies']);
    Route::get('/pharmacies/{pharmacy}', [AdminController::class, 'show']);
    Route::put('/pharmacies/{pharmacy}/approve', [AdminController::class, 'approve']);
    Route::delete('/pharmacies/{pharmacy}/reject', [AdminController::class, 'reject']);
    Route::post('/medicines/upload', [AdminController::class, 'uploadMedicines']);
    Route::get('/pharmacies/{id}/inventory', [AdminController::class, 'pharmacyInventory']);
    Route::get('/dashboard/stats', [AdminController::class, 'dashboardStats']);
    Route::get('/dashboard/top-pharmacies', [AdminController::class, 'topPharmacies']);
    Route::get('/dashboard/sales-trend', [AdminController::class, 'salesTrend']);
});

// Approved Pharmcies
Route::middleware(['auth:sanctum', 'pharmacy.approved'])->group(function () {
    Route::post('/inventory/upload', [InventoryController::class, 'upload']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications', [NotificationController::class, 'markAllAsRead']);
    Route::get('/pharmacy/stats', [StatController::class, 'dashboard']);
    Route::get('/pharmacy/top-medicines', [StatController::class, 'topMedicines']);
    Route::get('/pharmacy/sales-chart', [StatController::class, 'salesChart']);
    Route::get('/pharmacy/low-stock', [StatController::class, 'lowStock']);
});

Route::get('/search/medicines', [SearchController::class, 'searchMedicines']);
Route::post('/search/pharmacies', [SearchController::class, 'findPharmacies']);
