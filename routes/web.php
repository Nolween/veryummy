<?php

use App\Http\Controllers\UserController;
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
Route::controller(WelcomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
});
// Regroupement des méthods du controller de la page recette
Route::controller(RecipeCardController::class)->group(function () {
    Route::get('/recipe/show/{id}', 'show')->name('recipe.show');
    Route::post('/recipe/status/{id}', 'status')->name('recipe.status');
    Route::post('/recipe/comment/{id}', 'comment')->name('recipe.comment');
    Route::patch('/recipe/opinion/empty/{id}', 'emptyOpinion')->name('recipe-opinion.empty');
});
// Regroupement des méthods du controller d'exploration des recettes
Route::controller(ExplorationController::class)->group(function () {
    Route::get('/exploration', 'list')->name('exploration.list');
});
// Regroupement des méthods du controller des recettes crées
Route::controller(MyRecipesController::class)->group(function () {
    Route::get('/my-recipes', 'list')->name('my-recipes.list');
});
// Regroupement des méthods du controller des recettes enregistrées par l'utilisateur
Route::controller(MyNotebookController::class)->group(function () {
    Route::get('/my-notebook', 'list')->name('my-notebook.list');
});
// Regroupement des méthods du controller des informations utilisateur
Route::controller(UserController::class)->group(function () {
    Route::get('/my-account', 'edit')->name('my-account.edit');
    Route::put('/my-account/update', 'update')->name('my-account.update');
    Route::delete('/my-account/destroy', 'destroy')->name('my-account.destroy');
    Route::get('/admin/users/index/{type}', 'index')->name('admin-users.index');
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
    Route::get('/admin/recipes/list/{type}', 'list')->name('admin-recipes.list');
    Route::post('/admin/recipes/allow}', 'allow')->name('admin-recipes-allow');
    Route::post('/recipe/status', 'status')->name('recipes.status');
    Route::get('/recipe/new', 'new')->name('my-recipes.new');
    Route::put('/recipe/create', 'create')->name('my-recipes.create');
    Route::post('/recipe/update', 'update')->name('my-recipes.update');
    Route::get('/recipe/edit/{id}', 'edit')->name('my-recipes.edit');
});

// Regroupement des methods du controller de gestion des utilisateurs

Route::get('/registration', function () {
    return view('registration');
})->name('registration');

require __DIR__.'/auth.php';
