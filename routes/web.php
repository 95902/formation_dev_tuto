<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\AssureController;
use App\Http\Controllers\CompasController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GarantieController;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\SinistreController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/compas', CompasController::class)->name('compas');

Route::resource('assures', AssureController::class);
Route::resource('contrats', ContratController::class);
Route::resource('sinistres', SinistreController::class);

Route::post('contrats/{contrat}/garanties', [GarantieController::class, 'store'])->name('garanties.store');
Route::delete('contrats/{contrat}/garanties/{garantie}', [GarantieController::class, 'destroy'])->name('garanties.destroy');

Route::post('sinistres/{sinistre}/pieces', [PieceController::class, 'store'])->name('pieces.store');
Route::delete('sinistres/{sinistre}/pieces/{piece}', [PieceController::class, 'destroy'])->name('pieces.destroy');

Route::post('/api/ask', AskController::class)->name('ask');
Route::get('/api/stats', StatsController::class)->name('stats');
