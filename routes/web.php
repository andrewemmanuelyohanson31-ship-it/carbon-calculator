<?php

use App\Http\Controllers\CalculatorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::controller(CalculatorController::class)->group(function () {
    Route::get('/calculator', 'choose')->name('calculator.choose');
    Route::get('/calculator/form', 'form')->name('calculator.form');
    Route::post('/calculator', 'store')->middleware('throttle:20,1')->name('calculator.store');
    Route::get('/results/{calculation}', 'results')->whereUuid('calculation')->name('results');
    Route::get('/results/{calculation}/recommendations', 'recommendations')->whereUuid('calculation')->name('recommendations');
    Route::get('/history', 'history')->name('history');
});
