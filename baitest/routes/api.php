<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

auth()->loginUsingId(1);

Route::post('customers/create', [CustomerController::class, 'store'])->middleware('can:create,App\Models\Customer');
Route::patch('customers/update', [CustomerController::class, 'update'])->middleware('can:update,App\Models\Customer');
Route::delete('customers/delete', [CustomerController::class, 'destroy'])->middleware('can:delete,App\Models\Customer');
Route::get('customers/search', [CustomerController::class, 'search'])->name('customer.search');

Route::post('products/create', [ProductController::class, 'store'])->middleware('can:create,App\Models\Product');
Route::patch('products/update', [ProductController::class, 'update'])->middleware('can:update,App\Models\Product');
Route::delete('products/delete', [ProductController::class, 'destroy'])->middleware('can:delete,App\Models\Product');
Route::get('products/search', [ProductController::class, 'search'])->name('product.search');

Route::post('orders/create', [OrderController::class, 'store'])->middleware('can:create,App\Models\Order');
Route::patch('orders/update', [OrderController::class, 'update'])->middleware('can:update,App\Models\Order');
Route::delete('orders/delete', [OrderController::class, 'destroy'])->middleware('can:delete,App\Models\Order');
Route::get('orders/search', [OrderController::class, 'search'])->name('order.search');