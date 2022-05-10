<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeCardController extends Controller
{

    /**
     * Page d'accueil
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $response = [];
        // Récupération de la recette grâce à son od
        $response['recipe'] = Recipe::select('name', 'cooking_time as cookingTime', 'making_time as makingTime', 'image', 'score')
            ->withCount('steps') // Nombre d'étapes possède la recette
            ->withCount('ingredients') // Nombre d'ingrédients dans la recette 
            ->findOrFail($id);
        $response['ingredients'] =  Recipe::findOrFail($id)->ingredients;
        $response['steps'] =  Recipe::findOrFail($id)->steps;
        $response['comments'] =  Recipe::findOrFail($id)->opinions;
        return view('recipeshow', $response);
    }
}
