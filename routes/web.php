<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/exploration', function () {
    return view('exploration');
});
Route::get('/my-recipes', function () {
    return view('myrecipes');
});
Route::get('/recipe/view', function () {
    return view('recipeview');
});
Route::get('/recipe/new', function () {
    return view('recipenew');
});
