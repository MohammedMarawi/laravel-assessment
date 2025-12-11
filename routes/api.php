<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    // Public endpoints - no authentication required
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected endpoint - requires authentication
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});


Route::post('/webhook/stripe', [WebhookController::class, 'handle'])
    ->name('webhook.stripe');


Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');


Route::middleware('auth:sanctum')->group(function () {


    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/statistics', [SubscriptionController::class, 'statistics'])->name('subscriptions.statistics');
        Route::get('/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');
        Route::get('/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    });


    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/checkout', [PaymentController::class, 'createCheckout'])->name('payments.checkout');
    });
});
