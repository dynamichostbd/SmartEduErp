<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\BankSettlementController;

Route::view('/', 'layouts.frontend_app');
Route::view('/content/{slug}', 'layouts.frontend_app');
Route::view('/contactus', 'layouts.frontend_app');

Route::post('/sslcommerz/settlement', [BankSettlementController::class, 'settlement']);

Route::middleware('web')->prefix('admin')->group(base_path('routes/backend.php'));
