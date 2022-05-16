<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyRecipesController extends Controller
{
    /**
     * Listes des recettes de l'utilisateur
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
        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image', 'is_accepted')
            ->where('name', 'like', "%{$request->name}%")
            ->where('user_id', '=', $user->id)
            ->withCount('ingredients')
            ->withCount('steps');

        // Si on a un filtre sur le type de recette
        if ($request->type && $request->type > 0) {
            switch ((int)$request->type) {
                case 1: // Végétarien
                    $recipesCount = $recipes =  $recipes->where('vegetarian_compatible', 1);
                    break;
                case 2: // Vegan
                    $recipesCount = $recipes =  $recipes->where('vegan_compatible', 1);
                    break;
                case 3: // Sans gluten
                    $recipesCount = $recipes =  $recipes->where('gluten_free_compatible', 1);
                    break;
                case 4: // Halal
                    $recipesCount = $recipes =  $recipes->where('halal_compatible', 1);
                    break;
                case 5: // casher
                    $recipesCount = $recipes =  $recipes->where('kosher_compatible', 1);
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

        return view('myrecipes', $response);
    }
}
