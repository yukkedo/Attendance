<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAttendanceRequestController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\TimeClockController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/login', [AuthController::class, 'loginShow'])->name('login');
Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'applyList']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [TimeClockController::class, 'index']);
    Route::post('/attendance/clockIn', [TimeClockController::class, 'clockIn']);
    Route::post('/attendance/clockOut', [TimeClockController::class, 'clockOut']);
    Route::post('/attendance/breakStart', [TimeClockController::class, 'breakStart']);
    Route::post('/attendance/breakEnd', [TimeClockController::class, 'breakEnd']);
    Route::get('/attendance/list', [AttendanceController::class, 'index']);
    Route::get('/attendance/list/{month?}', [AttendanceController::class, 'index']);
    Route::post('/attendance/{id}', [AttendanceRequestController::class, 'requestChange']);
});

Route::prefix('admin')->group(function() {
    Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
});

Route::middleware('auth:admin')->group(function() {
    Route::post('/admin/logout', [AdminLoginController::class, 'logout']);
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
    Route::get('/admin/attendance/list/{date?}', [AdminAttendanceController::class, 'index']);
    // Route::post('/attendance/{id}', [AdminAttendanceRequestController::class, 'requestChange']);
    Route::get('/admin/staff/list', [AdminStaffController::class, 'list']);
    Route::get('/admin/attendance/staff/{user}/{month?}', [AdminStaffController::class, 'staffAttendanceList']);
    Route::get('/admin/attendance/staff/{user}/export/{month?}', [AdminStaffController::class, 'export']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceRequestController::class, 'viewApproved'])->name('approve.view');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceRequestController::class, 'approve']);
});

Route::get('/attendance/{id}', function ($id) {
    if (Auth::guard('admin')->check()) {
        return app(AdminAttendanceRequestController::class)->getDetail($id);
    }
    if (Auth::guard('web')->check()) {
        return app(AttendanceRequestController::class)->getDetail($id);
    }
});