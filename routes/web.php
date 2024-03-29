<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
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

// Regroupement des méthods du controller des informations utilisateur
Route::controller(UserController::class)->group(function () {
    Route::get('/my-account', 'edit')->name('my-account.edit');
    Route::put('/my-account/update', 'update')->name('my-account.update');
    Route::delete('/my-account/destroy', 'destroy')->name('my-account.destroy');
    Route::get('/admin/users/index/{type}', 'index')->where(['type' => '[0-1]'])->name('admin-users.index');
    Route::patch('/admin/users/ban/', 'ban')->name('admin-users.ban');
    Route::delete('/admin/users/moderate/', 'moderate')->name('admin-users.moderate');
});
// Regroupement des méthods du controller des ingrédients enregistrés par l'utilisateur
Route::controller(IngredientController::class)->group(function () {
    Route::get('/admin/ingredients/index/{type}', 'index')->name('admin-ingredients.index');
    Route::post('/admin/ingredients/deny', 'deny')->name('admin-ingredients.deny');
    Route::post('/admin/ingredients/allow', 'allow')->name('admin-ingredients.allow');
    Route::get('/ingredients/create', 'create')->name('new-ingredient.create');
    Route::post('/ingredients/store', 'store')->name('new-ingredient.store');
});

// Regroupement des methods du controller des recettes enregistrées par l'utilisateur
Route::controller(RecipeController::class)->group(function () {
    Route::get('/', 'welcomeIndex')->name('home');
    Route::get('/exploration', 'explorationIndex')->name('exploration.index');
    Route::get('/admin/recipes/index/{type}', 'adminIndex')->name('admin-recipes.index');
    Route::patch('/admin/recipes/allow}', 'moderate')->name('admin.recipes.moderate');
    Route::post('/recipe/status', 'status')->name('recipe.status');
    Route::get('/recipe/create', 'create')->name('my-recipes.create');
    Route::put('/recipe/store', 'store')->name('my-recipes.store');
    Route::patch('/recipe/update', 'update')->name('my-recipes.update');
    Route::get('/recipe/edit/{id}', 'edit')->name('my-recipes.edit');
    Route::get('/recipe/show/{id}', 'show')->name('recipe.show');
    Route::post('/recipe/comment/{recipe}', 'comment')->name('recipe.comment');
    Route::patch('/recipe/opinion/empty/{recipe}', 'emptyOpinion')->name('recipe-opinion.empty');
    Route::get('/my-recipes', 'userIndex')->name('my-recipes.list');
    Route::get('/my-notebook', 'noteBookIndex')->name('my-notebook.list');

});

// Regroupement des methods du controller de gestion des utilisateurs

Route::get('/registration', function () {
    return view('registration');
})->name('registration');

require __DIR__.'/auth.php';
