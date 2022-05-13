<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

/**
 * Page d'accueil
 *
 * @return \Illuminate\View\View
 */
class ExplorationController extends Controller
{
    public function list(Request $request)
    {
        $response = [];

        $test = $request->validate([
            'name' => ['string', 'nullable'],
            'type' => ['integer', 'nullable'],
        ]);

        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
            ->where('is_accepted', true)
            ->where('name', 'like', "%{$request->name}%")
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

        return view('exploration', $response);
    }
}
