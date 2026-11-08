<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/layanan-produk', [HomeController::class, 'catalog'])->name('catalog');

// ---------- Autentikasi ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registrasi', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registrasi', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Webhook Midtrans (publik, diverifikasi dengan signature) ----------
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');

// ---------- Data slot (JSON) untuk pelanggan & admin ----------
Route::get('/api/slots', [Customer\BookingController::class, 'slots'])->middleware('auth')->name('slots.json');

// ---------- Pelanggan ----------
Route::middleware(['auth', 'role:pelanggan'])->group(function () {
    Route::get('/booking', [Customer\BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [Customer\BookingController::class, 'store'])->name('booking.store');

    Route::get('/pesanan', [Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{booking}', [Customer\OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{booking}/bayar', [Customer\OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/pesanan/{booking}/batal', [Customer\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{booking}/snap', [Customer\OrderController::class, 'snap'])->name('orders.snap');
    Route::post('/pesanan/{booking}/cod', [Customer\OrderController::class, 'cod'])->name('orders.cod');
    Route::post('/pesanan/{booking}/cek-pembayaran', [Customer\OrderController::class, 'check'])->name('orders.check');
});

// ---------- Admin ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{booking}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{booking}/status', [Admin\OrderController::class, 'status'])->name('orders.status');
    Route::post('/pesanan/{booking}/batal', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{booking}/cod', [Admin\OrderController::class, 'confirmCod'])->name('orders.cod');
    Route::get('/pesanan/{booking}/jadwal', [Admin\OrderController::class, 'rescheduleForm'])->name('orders.reschedule');
    Route::post('/pesanan/{booking}/jadwal', [Admin\OrderController::class, 'reschedule'])->name('orders.reschedule.store');

    Route::resource('layanan', Admin\ServiceController::class)->parameters(['layanan' => 'service'])->except('show')->names('services');
    Route::resource('produk', Admin\ProductController::class)->parameters(['produk' => 'product'])->except('show')->names('products');

    Route::get('/jadwal', [Admin\ScheduleController::class, 'index'])->name('schedule');
    Route::put('/jadwal/jam', [Admin\ScheduleController::class, 'updateHours'])->name('schedule.hours');
    Route::patch('/jadwal/slot/{slot}', [Admin\ScheduleController::class, 'updateSlot'])->name('schedule.slot');
});

// ---------- Owner ----------
Route::prefix('owner')->name('owner.')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/', [Owner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [Owner\ReportController::class, 'index'])->name('reports');
    Route::get('/laporan/ekspor', [Owner\ReportController::class, 'export'])->name('reports.export');
});
