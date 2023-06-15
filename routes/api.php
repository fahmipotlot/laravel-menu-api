<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\RecipeController;
use App\Http\Controllers\Api\v1\MenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function () {    
    Route::group(['middleware' => ['api']], function () {
        Route::resource('category', CategoryController::class);
        Route::resource('recipe', RecipeController::class);
        Route::resource('menu', MenuController::class);
    });
});
