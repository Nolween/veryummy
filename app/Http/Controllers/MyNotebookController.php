<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeType;
use App\Rules\DietExists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyNotebookController extends Controller
{
    /**
     * Listes des recettes favories de l'utilisateur
     */
    public function list(Request $request): View|RedirectResponse
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
        $recipes = Recipe::select('*')->join('recipe_opinions', 'recipe_opinions.recipe_id', '=', 'recipes.id')
            ->where('recipes.name', 'like', "%{$request->name}%")
            ->where('recipe_opinions.user_id', '=', $user->id)
            ->where('recipe_opinions.is_favorite', true)
            ->withCount('ingredients')
            ->withCount('steps');

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->typeId && (int) $request->typeId > 0) {
            $recipes = $recipes->where('recipes.recipe_type_id', $request->typeId);
        }

        // Si on a un filtre sur le type de régime
        if ($request->diet && $request->diet > 0) {
            switch ((int) $request->diet) {
                case 1: // Végétarien
                    $recipesCount = $recipes = $recipes->where('recipes.vegetarian_compatible', 1);
                    break;
                case 2: // Vegan
                    $recipesCount = $recipes = $recipes->where('recipes.vegan_compatible', 1);
                    break;
                case 3: // Sans gluten
                    $recipesCount = $recipes = $recipes->where('recipes.gluten_free_compatible', 1);
                    break;
                case 4: // Halal
                    $recipesCount = $recipes = $recipes->where('recipes.halal_compatible', 1);
                    break;
                case 5: // casher
                    $recipesCount = $recipes = $recipes->where('recipes.kosher_compatible', 1);
                    break;
                default:
                    $recipesCount = $recipes;
                    break;
            }
            $response['total'] = $recipesCount->count();
        }
        // Si pas de filtre de régime
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
        $response['search'] = $request->name ?? null;
        $response['diet'] = $request->diet ?? null;
        $response['typeId'] = $request->typeId ?? null;

        return view('mynotebook', $response);
    }
}
