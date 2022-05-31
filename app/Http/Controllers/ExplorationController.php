<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

/**
 * Page d'accueil
 *
 * @return \Illuminate\View\View
 */
class ExplorationController extends Controller
{

    /**
     * Listes des recettes
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $response = [];

        $userId = Auth::id() ?? null;

        $test = $request->validate([
            'name' => ['string', 'nullable'],
            'typeId' => ['integer', 'nullable'],
            'diet' => ['integer', 'nullable'],
        ]);

        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
            ->where('name', 'like', "%{$request->name}%")
            ->withCount('ingredients')
            ->withCount('steps');
        // Si utilisateur connecté, on ne poublie pas ses recettes
        if ($userId) {
            $recipes->where('user_id', '!=', $userId)->with('user')
            ->with('opinion');
        }

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->typeId && (int)$request->typeId > 0) {
            $recipes =  $recipes->where('recipe_type_id', $request->typeId);
        }


        // Si on a un filtre sur le type de régime
        if ($request->diet && $request->diet > 0) {
            switch ((int)$request->diet) {
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
        // Création d'un type temporaire tous
        $allTypes = new RecipeType();
        $allTypes->id = 0;
        $allTypes->name = 'Tous';
        // Récupération de tous les types de plat auquel on ajoute le type tous
        $response['types'] = RecipeType::all()->prepend($allTypes);
        // dd($response['types']);
        // Renvoi des données de filtres de recherche
        $response['search'] = $request->name ?? null;
        $response['diet'] = $request->diet ?? null;
        $response['typeId'] = $request->typeId ?? null;

        return view('exploration', $response);
    }
}
