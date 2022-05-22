<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ExplorationController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MyNotebookController;
use App\Http\Controllers\MyRecipesController;
use App\Http\Controllers\RecipeCardController;
use App\Http\Controllers\RecipeController;
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
    Route::patch('/recipe/opinion/empty/{id}', 'emptyOpinion')->name('recipe-opinion.empty');
});
// Regroupement des méthods du controller d'exploration des recettes
Route::controller(ExplorationController::class)->group(function() {
    Route::get('/exploration', 'list')->name('exploration.list');
});
// Regroupement des méthods du controller des recettes crées
Route::controller(MyRecipesController::class)->group(function() {
    Route::get('/my-recipes', 'list')->name('my-recipes.list');
});
// Regroupement des méthods du controller des recettes enregistrées par l'utilisateur
Route::controller(MyNotebookController::class)->group(function() {
    Route::get('/my-notebook', 'list')->name('my-notebook.list');
});
// Regroupement des méthods du controller des informations utilisateur
Route::controller(AccountController::class)->group(function() {
    Route::get('/my-account', 'show')->name('my-account.show');
    Route::put('/my-account/edit', 'edit')->name('my-account.edit');
    Route::delete('/my-account/delete', 'delete')->name('my-account.delete');
    Route::get('/admin/users/list/{type}', 'list')->name('admin-users.list');
    Route::delete('/admin/users/ban/', 'ban')->name('admin-users.ban');
    Route::delete('/admin/users/moderate/', 'moderate')->name('admin-users.moderate');
});
// Regroupement des méthods du controller des ingrédients enregistrés par l'utilisateur
Route::controller(IngredientController::class)->group(function() {
    Route::get('/admin/ingredients/list/{type}', 'list')->name('admin-ingredients.list');
    Route::post('/admin/ingredients/allow', 'allow')->name('admin-ingredients-allow');
});

// Regroupement des methods du controller des recettes enregistrées par l'utilisateur
Route::controller(RecipeController::class)->group(function() {
    Route::get('/admin/recipes/list/{type}', 'list')->name('admin-recipes.list');
    Route::post('/admin/recipes/allow}', 'allow')->name('admin-recipes-allow');
    Route::post('/recipe/status', 'status')->name('recipes.status');
});

// Regroupement des methods du controller de gestion des utilisateurs


Route::get('/recipe/new', function () {
    return view('recipenew');
})->name('my-recipes.new');
Route::get('/recipe/edit/{id}', function ($id) {
    return view('recipeedit');
})->name('my-recipes.edit');
Route::get('/registration', function () {
    return view('registration');
})->name('registration');



require __DIR__.'/auth.php';
