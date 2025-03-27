<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pages\Customer\StatusController;
use App\Http\Controllers\Pages\Customer\TypeController;
use App\Http\Controllers\Pages\CustomerController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\FinanceController;
use App\Http\Controllers\Pages\KategoriController;
use App\Http\Controllers\Pages\ProductController;
use App\Http\Controllers\Pages\TransactionController;
use App\Http\Controllers\Pages\UsahaController;
use App\Http\Controllers\Pages\UserController;
use App\Http\Controllers\Role\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('post.login');

Route::middleware(['auth'])->group(function() {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('roles')->group(function() {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index')->can('lihat level');
        Route::post('/store', [RoleController::class, 'store'])->name('roles.store')->can('tambah level');
        Route::get('/{id}/show', [RoleController::class, 'show'])->name('roles.show')->can('edit level');
        Route::put('/{id}/update', [RoleController::class, 'update'])->name('roles.update')->can('edit level');
        Route::delete('/{id}/destroy', [RoleController::class, 'destroy'])->name('roles.destroy')->can('hapus level');
        Route::get('/{id}/permission', [RoleController::class, 'permission'])->name('roles.permission')->can('edit level');
        Route::put('/{id}/savePermission', [RoleController::class, 'savePermission'])->name('roles.savePermission')->can('edit level');
    });
    
    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('user.index')->can('lihat user');
        Route::get('/create', [UserController::class, 'create'])->name('user.create')->can('tambah user');
        Route::post('/store', [UserController::class, 'store'])->name('user.store')->can('tambah user');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->can('edit user');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('user.update')->can('edit user');
        Route::delete('/{id}/destroy', [UserController::class, 'destroy'])->name('user.destroy')->can('hapus user');
    });

    Route::prefix('categories')->group(function() {
        Route::get('/', [KategoriController::class, 'index'])->name('kategori.index')->can('lihat kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('kategori.store')->can('tambah kategori');
        Route::get('/{id}/show', [KategoriController::class, 'show'])->name('kategori.show')->can('edit kategori');
        Route::put('/{id}/update', [KategoriController::class, 'update'])->name('kategori.update')->can('edit kategori');
        Route::delete('/{id}/destroy', [KategoriController::class, 'destroy'])->name('kategori.destroy')->can('hapus kategori');
    });

    Route::prefix('products')->group(function() {
        Route::get('/', [ProductController::class, 'index'])->name('produk.index')->can('lihat barang');
        Route::post('/store', [ProductController::class, 'store'])->name('produk.store')->can('tambah barang');
        Route::get('/{id}/show', [ProductController::class, 'show'])->name('produk.show')->can('edit barang');
        Route::put('/{id}/update', [ProductController::class, 'update'])->name('produk.update')->can('edit barang');
        Route::delete('/{id}/destroy', [ProductController::class, 'destroy'])->name('produk.destroy')->can('hapus barang');
    });

    Route::prefix('module-customer')->group(function() {
        Route::middleware(['role:Admin'])->group(function() {
            Route::prefix('types')->group(function() {
                Route::get('/', [TypeController::class, 'index'])->name('costumer.type.index');
                Route::post('/store', [TypeController::class, 'store'])->name('customer.type.store');
                Route::get('/{id}/show', [TypeController::class, 'show'])->name('customer.type.show');
                Route::put('/{id}/update', [TypeController::class, 'update'])->name('customer.type.update');
                Route::delete('/{id}/destroy', [TypeController::class, 'destroy'])->name('customer.type.destroy');
            });

            Route::prefix('status')->group(function() {
                Route::get('/', [StatusController::class, 'index'])->name('costumer.status.index');
                Route::post('/store', [StatusController::class, 'store'])->name('customer.status.store');
                Route::get('/{id}/show', [StatusController::class, 'show'])->name('customer.status.show');
                Route::put('/{id}/update', [StatusController::class, 'update'])->name('customer.status.update');
                Route::delete('/{id}/destroy', [StatusController::class, 'destroy'])->name('customer.status.destroy');
            });
        });
        
        Route::prefix('customers')->group(function() {
            Route::get('/', [CustomerController::class, 'index'])->name('customer.index')->can('lihat pelanggan');
            Route::post('/store', [CustomerController::class, 'store'])->name('customer.store')->can('tambah pelanggan');
            Route::get('/{id}/show', [CustomerController::class, 'show'])->name('customer.show')->can('edit pelanggan');
            Route::put('/{id}/update', [CustomerController::class, 'update'])->name('customer.update')->can('edit pelanggan');
            Route::delete('/{id}/destroy', [CustomerController::class, 'destroy'])->name('customer.destroy')->can('hapus pelanggan');
            Route::get('/export', [CustomerController::class, 'export'])->name('customer.export')->can('lihat pelanggan');
        });
    });

    Route::prefix('transaction')->group(function() {
        Route::get('/', [TransactionController::class, 'index'])->name('transaksi.index')->can('lihat transaksi');
        Route::get('/create', [TransactionController::class, 'create'])->name('transaksi.create')->can('tambah transaksi');
        Route::post('/getCustomerBySerialNumber', [TransactionController::class, 'getCustomerBySerialNumber'])->name('transaksi.getCustomerBySerialNumber')->can('tambah transaksi');
        Route::post('/store', [TransactionController::class, 'store'])->name('transaksi.store')->can('tambah transaksi');
        Route::get('/{id}/receipt', [TransactionController::class, 'show'])->name('transaksi.show')->can('lihat transaksi');
        Route::get('/export', [TransactionController::class, 'export'])->name('transaksi.export')->can('lihat transaksi');
    });

    Route::prefix('finances')->group(function() {
        Route::get('/', [FinanceController::class, 'index'])->name('keuangan.index')->can('lihat keuangan');
        Route::get('/export/{start_date}/{end_date}', [FinanceController::class, 'export'])->name('keuangan.export')->can('lihat keuangan');
    });

    Route::prefix('settings')->group(function() {
        Route::get('/', [UsahaController::class, 'index'])->name('usaha.index');
        Route::post('/store', [UsahaController::class, 'store'])->name('usaha.store');
    });
});