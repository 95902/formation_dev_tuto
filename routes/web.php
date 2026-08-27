<?php

use App\Http\Controllers\AskController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/up'));

Route::post('/api/ask', AskController::class)->name('ask');

Route::get('/api/stats', App\Http\Controllers\StatsController::class)->name('stats');
