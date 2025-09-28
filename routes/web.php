<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\EconomiesController;
use App\Http\Middleware\CheckIfLogged;
use App\Http\Middleware\CheckIsLogged;
use Illuminate\Support\Facades\Route;

// auth routes

Route::middleware([CheckIfLogged::class])->group(function(){
    
    Route::get('/prohibited', [AuthController::class, 'login']);
    Route::post('/loginSubmit', [AuthController::class, 'loginSubmit']);
    
});

// 
Route::middleware([CheckIsLogged::class])->group(function(){
    
    Route::get('/', [MainController::class, 'index']);
    Route::get('/newNote', [MainController::class, 'newNote']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/send_values', function(){
        return view('send_values');
    });
    

});


Route::post('/economies', [EconomiesController::class, 'store'])->name('economies.store'); 


