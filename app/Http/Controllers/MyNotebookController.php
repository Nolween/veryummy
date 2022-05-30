<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyNotebookController extends Controller
{
    
    /**
     * Listes des recettes favories de l'utilisateur
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $response = [];

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if(!$user) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }
        // Validation du formulaire
        $test = $request->validate([
            'name' => ['string', 'nullable'],
            'type' => ['integer', 'nullable'],
        ]);

        // Début de la requête
        $recipes = Recipe::join('recipe_opinions', 'recipe_opinions.recipe_id', '=', 'recipes.id')
            ->where('recipes.name', 'like', "%{$request->name}%")
            ->where('recipe_opinions.user_id', '=', $user->id)
            ->where('recipe_opinions.is_favorite', true)
            ->withCount('ingredients')
            ->withCount('steps');

        // Si on a un filtre sur le type de recette
        if ($request->type && $request->type > 0) {
            switch ((int)$request->type) {
                case 1: // Végétarien
                    $recipesCount = $recipes =  $recipes->where('recipes.vegetarian_compatible', 1);
                    break;
                case 2: // Vegan
                    $recipesCount = $recipes =  $recipes->where('recipes.vegan_compatible', 1);
                    break;
                case 3: // Sans gluten
                    $recipesCount = $recipes =  $recipes->where('recipes.gluten_free_compatible', 1);
                    break;
                case 4: // Halal
                    $recipesCount = $recipes =  $recipes->where('recipes.halal_compatible', 1);
                    break;
                case 5: // casher
                    $recipesCount = $recipes =  $recipes->where('recipes.kosher_compatible', 1);
                    break;

                default:
                    break;
            }
            $response['total'] = $recipesCount->count();
        }
        // Si pas de filtre de type
        else {
            $response['total'] = $recipes->count();
        }

        // Pagination des recettes
        $response['recipes'] = $recipes->paginate(20);

        // Renvoi des données de filtres de recherche
        $response['search'] = $request->name ?? null;
        $response['type'] = $request->type ?? null;

        return view('mynotebook', $response);
    }
}
