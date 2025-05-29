<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\TimeClockController;
use App\Http\Controllers\AuthController;
use App\Models\Attendance;
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

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [TimeClockController::class, 'index']);
    Route::post('/attendance/clockIn', [TimeClockController::class, 'clockIn']);
    Route::post('/attendance/clockOut', [TimeClockController::class, 'clockOut']);
    Route::post('/attendance/breakStart', [TimeClockController::class, 'breakStart']);
    Route::post('/attendance/breakEnd', [TimeClockController::class, 'breakEnd']);
    Route::get('/attendance/list', [AttendanceController::class, 'index']);
    Route::get('/attendance/list/{month?}', [AttendanceController::class, 'index']);
    Route::get('/attendance/{id}', [AttendanceRequestController::class, 'getDetail']);
    Route::post('/attendance/{id}', [AttendanceRequestController::class, 'requestChange']);
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'applyList']);
});