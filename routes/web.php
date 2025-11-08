<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\EconomiesController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Middleware\CheckIfLogged;
use App\Http\Middleware\CheckIsLogged;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjecoesController;
use App\Http\Controllers\ProfileController;

// ------------------- AUTH -------------------
Route::middleware([CheckIfLogged::class])->group(function () {
    Route::get('/prohibited', [AuthController::class, 'login']);
    Route::post('/loginSubmit', [AuthController::class, 'loginSubmit']);
    
    // Register
    Route::post('/registerSubmit', [AuthController::class, 'registerSubmit'])->name('register.submit');
});

// ------------------- APP --------------------
Route::middleware([CheckIsLogged::class])->group(function () {

    // Projeções Analytics
    Route::get('/projecoes', [ProjecoesController::class, 'index'])->name('projecoes.index');
    Route::get('/projecoes/data', [ProjecoesController::class, 'data'])->name('projecoes.data');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Home & sessão
    Route::get('/', [MainController::class, 'index'])->name('home');
    Route::get('/newNote', [MainController::class, 'newNote']); // se usa
    Route::get('/logout', [AuthController::class, 'logout']);

    // ====== RENDAS ======
    // Listagem com filtro/ordem
    Route::get('/rendas', [EconomiesController::class, 'show'])->name('economies.show');

    // Tela limpa para criar várias rendas de uma vez
    Route::get('/rendas/create', [EconomiesController::class, 'create'])->name('economies.create');
    Route::post('/rendas', [EconomiesController::class, 'storeMany'])->name('economies.storeMany');

    // Editar UMA renda
    Route::get('/rendas/{id}/edit', [EconomiesController::class, 'edit'])->name('economies.edit');
    Route::put('/rendas/{id}', [EconomiesController::class, 'update'])->name('economies.update');

    // Excluir
    Route::delete('/rendas/{id}', [EconomiesController::class, 'destroy'])->name('economies.destroy');

    // (Opcional) manter URL antiga funcionando:
    Route::get('/send_values', fn () => redirect()->route('economies.create'));

    // Saldo (rendas - gastos)
    Route::get('/saldo', [EconomiesController::class, 'saldo'])->name('economies.saldo');

    // ====== GASTOS ======
    Route::get('/gastos', [GastosController::class, 'index'])->name('gastos.index');
    Route::get('/send_expenses', [GastosController::class, 'create'])->name('gastos.create'); // tela limpa
    Route::post('/gastos/store', [GastosController::class, 'store'])->name('gastos.store');
    Route::delete('/gastos/{id}', [GastosController::class, 'destroy'])->name('gastos.destroy');
});

// ------------------- LEGACY (remova) -------------------
// ❌ Remova essa rota antiga para não conflitar com o novo fluxo:
// Route::post('/economies', [EconomiesController::class, 'store'])->name('economies.store');
