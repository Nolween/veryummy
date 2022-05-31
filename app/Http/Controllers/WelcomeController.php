<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class WelcomeController extends Controller
{

    /**
     * Page d'accueil
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $response = [];
        // Récupération de 4 recettes au hasard avec plus de 4 en note
        $response['popularRecipes'] = Recipe::select('id', 'name', 'cooking_time as cookingTime', 'making_time as makingTime', 'image as photo', 'score')
        ->withCount('steps') // Nombre d'étapes possède la recette
        ->withCount('ingredients') // Nombre d'ingrédients dans la recette 
        ->where('score', '>', 4) // Avec une note supérieure à 4
        ->inRandomOrder() // Recette au hasard
        ->take(4) // 4 recettes
        ->get();

        // Récupération des 4 dernières recettes créées par les utilisateurs
        $response['recentRecipes'] = Recipe::select('id', 'name', 'cooking_time as cookingTime', 'making_time as makingTime', 'image as photo', 'score')
        ->withCount('steps') // Nombre d'étapes possède la recette
        ->withCount('ingredients') // Nombre d'ingrédients dans la recette 
        ->orderBy('created_at', 'DESC') // Classées par ordre de création décroissant
        ->take(4) // 4 recettes
        ->get();
        // Compteur des informations
        $response['counts'] = [
            'totalRecipes' => Recipe::where('is_accepted', true)->count(),
            'totalIngredients' => Ingredient::count(),
            'totalUsers' => User::where('is_banned', false)->count()
        ];

        return view('welcome', $response);
    }
}
