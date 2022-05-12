<?php

use App\Http\Controllers\RecipeCardController;
use App\Http\Controllers\WelcomeController;
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

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');


// Regroupement des méthods du controller de la page d'accueil
Route::controller(WelcomeController::class)->group(function() {
    Route::get('/', 'index')->name('home');
});
// Regroupement des méthods du controller de la page recette
Route::controller(RecipeCardController::class)->group(function() {
    Route::get('/recipe/show/{id}', 'show')->name('recipe.show');
    Route::post('/recipe/status/{id}', 'status')->name('recipe.status');
    Route::post('/recipe/comment/{id}', 'comment')->name('recipe.comment');
});
Route::get('/exploration', function () {
    return view('exploration');
})->name('exploration.list');
Route::get('/my-notebook', function () {
    return view('mynotebook');
})->name('my-notebook.list');
Route::get('/my-recipes', function () {
    return view('myrecipes');
})->name('my-recipes.list');
Route::get('/recipe/new', function () {
    return view('recipenew');
})->name('my-recipes.new');
Route::get('/recipe/edit/{id}', function ($id) {
    return view('recipeedit');
})->name('my-recipes.edit');
Route::get('/registration', function () {
    return view('registration');
})->name('registration');
Route::get('/my-account', function () {
    return view('myaccount');
})->name('myaccount');
Route::get('/admin/ingredients/list', function () {
    return view('admin-ingredientslist');
})->name('admin-ingredientslist');
Route::get('/admin/recipes/list', function () {
    return view('admin-recipeslist');
})->name('admin-recipeslist');
Route::get('/admin/users/list', function () {
    return view('admin-userslist');
})->name('admin-userslist');



require __DIR__.'/auth.php';
