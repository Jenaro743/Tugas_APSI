<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/product/{product}', [StoreController::class, 'product'])->name('product');
Route::post('/guest', [StoreController::class, 'guest'])->name('guest');
Route::post('/guest/exit', [StoreController::class, 'exitGuest'])->name('guest.exit');
Route::get('/login', [StoreController::class, 'login'])->name('login');
Route::post('/login', [StoreController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.post');
Route::post('/logout', [StoreController::class, 'logout'])->name('logout');
Route::get('/cart', [StoreController::class, 'cartGuest'])->name('cart');
Route::post('/cart/{product}', [StoreController::class, 'addCart'])->name('cart.add');
Route::patch('/cart/{product}', [StoreController::class, 'updateCart'])->name('cart.update');
Route::delete('/cart/{product}', [StoreController::class, 'removeCart'])->name('cart.remove');
Route::post('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::get('/payment/{order}', [StoreController::class, 'payment'])->name('payment');
Route::get('/receipt/{order}', [StoreController::class, 'receipt'])->name('receipt');
Route::get('/dashboard', [StoreController::class, 'dashboard'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::middleware('role:admin,employee')->group(function () {
        Route::post('/orders/{order}/status', [StoreController::class, 'orderStatus'])->name('orders.status');
        Route::post('/products', [StoreController::class, 'createProduct'])->name('products.create');
        Route::put('/products/{product}', [StoreController::class, 'editProduct'])->name('products.edit');
        Route::delete('/products/{product}', [StoreController::class, 'deleteProduct'])->name('products.delete');
        Route::post('/products/{product}/stock', [StoreController::class, 'stock'])->name('products.stock');
    });
    Route::middleware('role:admin')->group(function () {
        Route::post('/staff', [StoreController::class, 'createStaff'])->name('staff.create');
    });
});
