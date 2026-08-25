<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BatchManagementController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\BookingRequestController;
use App\Http\Controllers\Admin\CoachMatchingController;
use App\Http\Controllers\Admin\CoachRosterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeactivationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Coach\PortalController as CoachPortalController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ManageBookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Camp FreedivePH
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. PUBLIC CUSTOMER PORTAL
// =========================================================================
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/api/weather/check', [BookingController::class, 'checkWeather'])->name('api.weather.check');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

Route::get('/manage-booking', [ManageBookingController::class, 'index'])->name('manage.index');
Route::post('/manage-booking/search', [ManageBookingController::class, 'search'])->name('manage.search');
Route::get('/manage-booking/{booking_number}', [ManageBookingController::class, 'show'])->name('manage.show');
Route::post('/manage-booking/{booking_number}/reschedule', [ManageBookingController::class, 'reschedule'])->name('manage.reschedule');
Route::post('/manage-booking/{booking_number}/cancel', [ManageBookingController::class, 'cancel'])->name('manage.cancel');

// Weather Safety Forecast Instant Preview (Client date selection)
Route::get('/api/weather/preview', [\App\Http\Controllers\Api\WeatherPreviewController::class, 'preview'])->name('api.weather.preview');

// PayMongo Customer Payment Integration
Route::post('/booking/{booking}/paymongo/checkout', [\App\Http\Controllers\Payment\PayMongoController::class, 'checkout'])->name('paymongo.checkout');
Route::get('/booking/{booking}/paymongo/success', [\App\Http\Controllers\Payment\PayMongoController::class, 'success'])->name('paymongo.success');
Route::get('/booking/{booking}/paymongo/cancel', [\App\Http\Controllers\Payment\PayMongoController::class, 'cancel'])->name('paymongo.cancel');

// PayMongo Webhook Endpoints
Route::post('/api/webhooks/paymongo', [\App\Http\Controllers\Payment\PayMongoController::class, 'webhook'])->name('paymongo.webhook.api');
Route::post('/webhooks/paymongo', [\App\Http\Controllers\Payment\PayMongoController::class, 'webhook'])->name('paymongo.webhook');

// =========================================================================
// 2. INTERNAL AUTHENTICATION (STAFF ROUTES)
// =========================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::get('/staff/login', [LoginController::class, 'showLoginForm']);
Route::get('/staff', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// =========================================================================
// 3. AUTHENTICATED STAFF ROUTES (SHARED)
// =========================================================================
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // First-login mandatory password change
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force_change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'update'])->name('password.force_change.update');
});

// =========================================================================
// 4. ADMIN & OWNER PORTAL (PROTECTED)
// =========================================================================
Route::middleware(['auth', 'active', 'must_change_password', 'role:owner,admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Booking Management
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
        
        // Pending Customer Requests Queue
        Route::get('/bookings/requests', [BookingRequestController::class, 'index'])->name('bookings.requests');
        Route::post('/bookings/requests/reschedule/{rescheduleRequest}/approve', [BookingRequestController::class, 'approveReschedule'])->name('bookings.requests.reschedule.approve');
        Route::post('/bookings/requests/reschedule/{rescheduleRequest}/reject', [BookingRequestController::class, 'rejectReschedule'])->name('bookings.requests.reschedule.reject');
        Route::post('/bookings/requests/cancellation/{cancellationRequest}/approve', [BookingRequestController::class, 'approveCancellation'])->name('bookings.requests.cancellation.approve');
        Route::post('/bookings/requests/cancellation/{cancellationRequest}/reject', [BookingRequestController::class, 'rejectCancellation'])->name('bookings.requests.cancellation.reject');

        // Booking Details, Status, and Edit
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
        Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status.update');

        // Batch Management Module (Admin / Owner)
        Route::get('/batches', [BatchManagementController::class, 'index'])->name('batches.index');
        Route::get('/batches/create', [BatchManagementController::class, 'create'])->name('batches.create');
        Route::post('/batches', [BatchManagementController::class, 'store'])->name('batches.store');
        Route::get('/batches/unbatched-bookings', [BatchManagementController::class, 'unbatchedBookings'])->name('batches.unbatched_bookings');
        Route::get('/batches/{batch}', [BatchManagementController::class, 'show'])->name('batches.show');
        Route::post('/batches/{batch}/status', [BatchManagementController::class, 'updateStatus'])->name('batches.update_status');
        Route::post('/batches/{batch}/move-booking', [BatchManagementController::class, 'moveBooking'])->name('batches.move_booking');

        // Payments & Refunds Module
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/refunds', [AdminRefundController::class, 'index'])->name('payments.refunds');
        Route::post('/payments/refunds/{refundRequest}/approve', [AdminRefundController::class, 'approve'])->name('payments.refunds.approve');
        Route::post('/payments/refunds/{refundRequest}/reject', [AdminRefundController::class, 'reject'])->name('payments.refunds.reject');
        Route::post('/payments/refunds/{refundRequest}/forfeit', [AdminRefundController::class, 'forfeit'])->name('payments.refunds.forfeit');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/settle-balance', [AdminPaymentController::class, 'settleBalance'])->name('payments.settle_balance');

        // Coaches Module (Admin / Owner)
        Route::get('/coaches', [CoachRosterController::class, 'index'])->name('coaches.index');
        Route::get('/coaches/matching', [CoachMatchingController::class, 'matching'])->name('coaches.matching');
        Route::post('/coaches/matching/assign', [CoachMatchingController::class, 'assign'])->name('coaches.matching.assign');
        Route::post('/coaches/matching/batch-assign', [CoachMatchingController::class, 'batchAssign'])->name('coaches.matching.batch_assign');
        Route::post('/coaches/matching/broadcast', [CoachMatchingController::class, 'broadcastOpening'])->name('coaches.matching.broadcast');
        Route::get('/coaches/requests', [CoachMatchingController::class, 'requests'])->name('coaches.requests');
        Route::post('/coaches/requests/{coachRequest}/approve', [CoachMatchingController::class, 'approveRequest'])->name('coaches.requests.approve');
        Route::get('/coaches/{coach}', [CoachRosterController::class, 'show'])->name('coaches.show');
        Route::post('/coaches/{coach}/reassign-student', [CoachRosterController::class, 'reassignStudent'])->name('coaches.reassign_student');

        // Weather & Marine Safety Monitoring Module (Admin / Owner)
        Route::get('/weather', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'index'])->name('weather.index');
        Route::post('/weather/sync-cache', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'syncCache'])->name('weather.sync_cache');
        Route::get('/weather/{batch}', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'show'])->name('weather.show');
        Route::post('/weather/{batch}/assess', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'assess'])->name('weather.assess');
        Route::post('/weather/{batch}/override', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'override'])->name('weather.override');
        Route::post('/weather/{batch}/cancel', [\App\Http\Controllers\Admin\WeatherSafetyController::class, 'cancel'])->name('weather.cancel');

        // User Management (Admin/Owner Managed Profile & Provisioning)
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/settings/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/settings/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/settings/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit']);
        Route::put('/settings/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/settings/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle_status');
        Route::delete('/settings/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        // Owner Exclusive Routes
        Route::middleware('role:owner')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
        });
    });

// =========================================================================
// 5. COACH PORTAL (PROTECTED)
// =========================================================================
Route::middleware(['auth', 'active', 'must_change_password', 'role:coach'])
    ->prefix('coach')
    ->name('coach.')
    ->group(function () {
        Route::get('/', [CoachPortalController::class, 'index'])->name('dashboard');
    });
