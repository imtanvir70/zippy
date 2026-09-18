<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourierWebhookController;

Route::post('/webhooks/courier/{provider}', [CourierWebhookController::class, 'handle'])->name('webhooks.courier');
