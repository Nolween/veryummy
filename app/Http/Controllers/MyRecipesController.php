<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeType;
use App\Rules\DietExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyRecipesController extends Controller
{
    /**
     * Listes des recettes de l'utilisateur
     *
     * @return void
     */
    public function list(Request $request)
    {
        $response = [];

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Si pas d'utilisateur
        if (! $user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }

        // Validation du formulaire
        $request->validate([
            'name' => ['string', 'nullable'],
            'typeId' => ['integer', 'exists:recipe_types,id', 'nullable'],
            'diet' => ['integer', 'nullable', new DietExists],
        ]);

        // Début de la requête
        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
            ->where('name', 'like', "%{$request->name}%")
            ->where('user_id', '=', $user->id)
            ->withCount('ingredients')
            ->withCount('steps');

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->typeId && (int) $request->typeId > 0) {
            $recipes = $recipes->where('recipe_type_id', $request->typeId);
        }

        // Si on a un filtre sur le type de recette
        if ($request->type && $request->type > 0) {
            switch ((int) $request->type) {
                case 1: // Végétarien
                    $recipesCount = $recipes = $recipes->where('vegetarian_compatible', 1);
                    break;
                case 2: // Vegan
                    $recipesCount = $recipes = $recipes->where('vegan_compatible', 1);
                    break;
                case 3: // Sans gluten
                    $recipesCount = $recipes = $recipes->where('gluten_free_compatible', 1);
                    break;
                case 4: // Halal
                    $recipesCount = $recipes = $recipes->where('halal_compatible', 1);
                    break;
                case 5: // casher
                    $recipesCount = $recipes = $recipes->where('kosher_compatible', 1);
                    break;

                default:
                    $recipesCount = 0;
                    break;
            }
            $response['total'] = $recipesCount->count() ?? 0;
        }
        // Si pas de filtre de type
        else {
            $response['total'] = $recipes->count();
        }

        // Création d'un type temporaire tous
        $allTypes = new RecipeType();
        $allTypes->id = 0;
        $allTypes->name = 'Tous';
        // Récupération de tous les types de plat auquel on ajoute le type tous
        $response['types'] = RecipeType::all()->prepend($allTypes);
        // Pagination des recettes
        $response['recipes'] = $recipes->paginate(20);
        // Renvoi des données de filtres de recherche
        $response['diet'] = $request->diet ?? null;
        $response['search'] = $request->name ?? null;
        $response['typeId'] = $request->typeId ?? null;

        return view('myrecipes', $response);
    }
}
