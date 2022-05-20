<?php

namespace App\Http\Controllers;

use App\Mail\RefusedRecipe;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RecipeController extends Controller
{

    public function list(int $type)
    {
        $response = [];

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role_id !== 1) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }

        switch ($type) {
            case 0:
                // Récupération des ingrédients
                $response['recipes'] = Recipe::having('opinions_count', ">", 0)
                    ->with('user')
                    ->with(['opinions' => function ($query) {
                        $query->where('is_reported', '=', true);
                    }])
                    ->withCount(['opinions' => function (Builder $query) {
                        $query->where('is_reported', '=', true);
                    }])
                    ->paginate(20);
                break;
            case 1:
                // Récupération des ingrédients
                $response['recipes'] = Recipe::having('opinions_count', "=", 0)
                    ->with('user')
                    ->withCount(['opinions' => function (Builder $query) {
                        $query->where('is_reported', '=', true);
                    }])
                    ->paginate(20);
                break;
            default:
                $type = null;
                break;
        }

        $response['typeList'] = (int)$type;
        // dd( $response['recipes']);

        return view('adminrecipeslist', $response);
    }

    
    public function allow(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role_id !== 1) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/");
        }
        $response = [];
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'recipeid' => ['integer', 'required'],
            'allow' => ['boolean', 'required'],
            'typeList' => ['integer', 'required'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de la recette par son Id
            $recipe = Recipe::where('id', (int)$request->recipeid)->with('user')->first();
            // Si pas de recette trouvée, erreur
            if (!$recipe) {
                return back()->with('recipeAllowError', 'Aucun ingrédient trouvé');
            }

            // Si on ignore les signalements
            if ($request->allow == true) {
                RecipeOpinion::where('recipe_id', (int)$request->recipeid)->where('is_reported', true)->update(array('is_reported' => false));
            }
            // Si 
            elseif ($request->allow == false) {
                $recipe->is_accepted = false;
                $recipe->save();
            }


            // Envoi de mail de désactivation à la personne ayant proposé la recette
            $informations = ['recipe' => $recipe->name, 'url' => URL::to('/')];
            // dd($informations);
            if ($request->allow == false) {
                Mail::to($user->email)->send(new RefusedRecipe($informations));
            }

            // Validation de la transaction
            DB::commit();
            return redirect("/admin/recipes/list/$request->typeList")->with('recipeAllowSuccess', "La recette a été modérée");
        }

        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return back()->with('recipeAllowError', "Erreur dans la modération de la recette");
        }
    }
}
