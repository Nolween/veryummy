<?php

namespace App\Repositories;

use App\Http\Requests\Recipe\RecipeAdminIndexRequest;
use App\Http\Requests\Recipe\RecipeAllowRequest;
use App\Http\Requests\Recipe\RecipeExplorationRequest;
use App\Http\Requests\Recipe\RecipeStatusRequest;
use App\Mail\RefusedRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ItemNotFoundException;

class RecipeRepository
{

    public function getWelcomeIndex()
    {
        $response = [];
        // Récupération de 4 recettes au hasard avec plus de 4 en note
        $response['popularRecipes'] = Recipe::select(
            'id',
            'name',
            'cooking_time as cookingTime',
            'making_time as makingTime',
            'image as photo',
            'score'
        )
                                            ->withCount('steps') // Nombre d'étapes possède la recette
                                            ->withCount('ingredients') // Nombre d'ingrédients dans la recette
                                            ->where('score', '>', 4) // Avec une note supérieure à 4
                                            ->inRandomOrder() // Recette au hasard
                                            ->take(4) // 4 recettes
                                            ->get();

        // Récupération des 4 dernières recettes créées par les utilisateurs
        $response['recentRecipes'] = Recipe::select(
            'id',
            'name',
            'cooking_time as cookingTime',
            'making_time as makingTime',
            'image as photo',
            'score'
        )
                                           ->withCount('steps') // Nombre d'étapes possède la recette
                                           ->withCount('ingredients') // Nombre d'ingrédients dans la recette
                                           ->orderBy('created_at', 'DESC') // Classées par ordre de création décroissant
                                           ->take(4) // 4 recettes
                                           ->get();
        // Compteur des informations
        $response['counts'] = [
            'totalRecipes'     => Recipe::where('is_accepted', true)->count(),
            'totalIngredients' => Ingredient::count(),
            'totalUsers'       => User::where('is_banned', false)->count(),
        ];

        return $response;
    }


    public function getExplorationIndex(RecipeExplorationRequest $request): array
    {
        $userId = Auth::user()->id;

        $response = [];

        $recipes = Recipe::select('id', 'name', 'score', 'making_time', 'cooking_time', 'image')
                         ->where('name', 'like', "%{$request->name}%")
                         ->withCount('ingredients')
                         ->withCount('steps');
        // Si utilisateur connecté, on n'oublie pas ses recettes
        if ($userId) {
            $recipes->where('user_id', '!=', $userId)->with('user')
                    ->with('opinion');
        }

        // Si on a un type de plat (entrée, plat, dessert,...)
        if ($request->typeId && (int)$request->typeId > 0) {
            $recipes = $recipes->where('recipe_type_id', $request->typeId);
        }

        // Si on a un filtre sur le type de régime
        if ($request->diet && $request->diet > 0) {
            switch ((int)$request->diet) {
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
                    $recipesCount = $recipes;
                    break;
            }
            $response['total'] = $recipesCount->count();
        } // Si pas de filtre de type
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

        return $response;
    }


    public function getAdminIndex(RecipeAdminIndexRequest $request, int $type): array
    {
        $response = [];

        switch ($type) {
            case 0:
                // Récupération des ingrédients
                $recipes = Recipe::having('opinions_count', '>', 0)
                                 ->with('user')
                                 ->with([
                                     'opinions' => function ($query) {
                                         $query->where('is_reported', '=', true);
                                     },
                                 ])
                                 ->withCount([
                                     'opinions' => function (Builder $query) {
                                         $query->where('is_reported', '=', true);
                                     },
                                 ]);

                // Si recherche
                if (!empty($request->search)) {
                    $recipes->where('name', 'like', "%{$request->search}%");
                }
                $response['recipes'] = $recipes->paginate(20);
                break;
            case 1:
                // Récupération des ingrédients
                $recipes = Recipe::having('opinions_count', '=', 0)
                                 ->with('user')
                                 ->withCount([
                                     'opinions' => function (Builder $query) {
                                         $query->where('is_reported', '=', true);
                                     },
                                 ]);

                // Si recherche
                if (!empty($request->search)) {
                    $recipes->where('name', 'like', "%{$request->search}%");
                }

                $response['recipes'] = $recipes->paginate(20);
                break;
            default:
                $type = null;
                $response['recipes'] = [];
                break;
        }

        $response['typeList'] = (int)$type;
        $response['search'] = $request->search;

        return $response;
    }


    public function moderateRecipe(RecipeAllowRequest $request): bool
    {
        $user = Auth::user();

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de la recette par son Id
            $recipe = Recipe::where('id', $request->recipeid)->with('user')->firstOrFail();

            // Si on ignore les signalements
            if ($request->allow == true) {
                RecipeOpinion::where('recipe_id', $request->recipeid)->where('is_reported', true)->update(
                    ['is_reported' => false]
                );
            } // Si on supprime la recette
            elseif ($request->allow == false) {
                Recipe::destroy($recipe->id);
                // Envoi de mail de désactivation à la personne ayant proposé la recette
                $informations = ['recipe' => $recipe->name, 'url' => URL::to('/')];
                Mail::to($user->email)->send(new RefusedRecipe($informations));
            }

            // Validation de la transaction
            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }


    public function updateStatus(RecipeStatusRequest $request) :bool
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // La recette existe t-elle?
            $recipe = Recipe::findOrFail($request->recipeid);

            RecipeOpinion::updateOrCreate(
                ['user_id' => $user->id, 'recipe_id' => $request->recipeid],
                ['is_favorite' => $request->is_favorite, 'is_reported' => $request->is_reported]
            );

            // Validation de la transaction
            DB::commit();

            return true;

        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;

        }
    }

}
