<?php

namespace App\Http\Controllers;

use App\Mail\AcceptedIngredient;
use App\Mail\RefusedIngredient;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class IngredientController extends Controller
{
    public function list(int $type, Request $request)
    {
        $response = [];

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (! $user || $user->role->name !== 'Administrateur' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }

        $response['typeList'] = $type;

        switch ($type) {
            case 0:
                $type = null;
                break;
            case 1:
                $type = true;
                break;
            case 2:
                $type = false;
                break;
            default:
                $type = null;
                break;
        }

        // Champ de recherche
        $request->validate([
            'search' => ['string', 'nullable'],
        ]);

        // Récupération des ingrédients
        $ingredients = Ingredient::where('is_accepted', $type)->with('user');
        // Si on a quelque chose dans la recherche
        if (! empty($request->search)) {
            $ingredients->where('name', 'like', "%{$request->search}%");
        }

        $response['ingredients'] = $ingredients->paginate(20);
        $response['search'] = $request->search ?? null;
        // dd( $response['ingredients']);

        return view('adminingredientslist', $response);
    }

    public function deny(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (! $user || $user->role->name !== 'Administrateur' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'ingredientid' => ['integer', 'required'],
            'deny' => ['accepted', 'required'],
            'typeList' => ['integer', 'required'],
            'denymessage' => ['string', 'required', 'min:2'],
        ]);

        if ($request->deny == 1) {
            // Transaction pour rollback si erreur
            DB::beginTransaction();
            try {
                // Récupération de l'ingrédient par son Id
                $ingredient = Ingredient::where('id', (int) $request->ingredientid)->with('user')->first();
                // Si pas d'ingrédient trouvé, erreur
                if (! $ingredient) {
                    return back()->withErrors(['ingredientAllowError' => 'Aucun ingrédient trouvé']);
                }
                $authorMail = $ingredient->user->email;
                $ingredient->is_accepted = false;
                // Si l'ingrédient est accepté, il passe sur le compte principal, en cas de suppression de compte du demandeur
                $ingredient->user_id = 1;
                $ingredient->save();

                // Envoi de mail à la personne ayant proposé l'ingrédient
                $informations = ['ingredient' => $ingredient->name, 'url' => URL::to('/'), 'message' => $request->denymessage];
                // Si la modération était en cours
                if ($request->typeList == 0) {
                    Mail::to($authorMail)->send(new RefusedIngredient($informations));
                }

                // Validation de la transaction
                DB::commit();

                return redirect("/admin/ingredients/list/$request->typeList")->with('ingredientAllowSuccess', "L'ingrédient a été modéré");
            }

            // Si erreur dans la transaction
            catch (QueryException $e) {
                DB::rollback();

                return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
            }
        }
    }

    public function allow(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (! $user || $user->role->name !== 'Administrateur' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'ingredientid' => ['integer', 'required'],
            'allow' => ['accepted', 'required'],
            'finalname' => ['string', 'required', 'min:2', 'max:255'],
            'typeList' => ['integer', 'required'],
            'vegetarian' => ['boolean', 'nullable'],
            'vegan' => ['boolean', 'nullable'],
            'glutenfree' => ['boolean', 'nullable'],
            'halal' => ['boolean', 'nullable'],
            'kosher' => ['boolean', 'nullable'],
        ]);

        if ($request->allow == 1) {
            // Transaction pour rollback si erreur
            DB::beginTransaction();
            try {
                // Récupération de l'ingrédient par son Id
                $ingredient = Ingredient::where('id', (int) $request->ingredientid)->with('user')->first();
                // Si pas d'ingrédient trouvé, erreur
                if (! $ingredient) {
                    return back()->withErrors(['ingredientAllowError' => 'Aucun ingrédient trouvé']);
                }
                $authorMail = $ingredient->user->email;
                $ingredient->name = $request->finalname;
                $ingredient->icon = Str::slug($request->finalname, '_');
                $ingredient->is_accepted = $request->allow;
                // Si l'ingrédient est accepté, il passe sur le compte principal, en cas de suppression de compte du demandeur
                $ingredient->user_id = 1;
                // Définition du régime de l'aliment
                $ingredient->vegetarian_compatible = $request->vegetarian ?? false;
                $ingredient->vegan_compatible = $request->vegan ?? false;
                $ingredient->gluten_free_compatible = $request->glutenfree ?? false;
                $ingredient->halal_compatible = $request->halal ?? false;
                $ingredient->kosher_compatible = $request->kosher ?? false;
                $ingredient->save();

                // Envoi de mail à la personne ayant proposé l'ingrédient
                $informations = ['ingredient' => $request->finalname, 'url' => URL::to('/')];
                if ($request->typeList == 0) {
                    Mail::to($authorMail)->send(new AcceptedIngredient($informations));
                }

                // Validation de la transaction
                DB::commit();

                return redirect("/admin/ingredients/list/$request->typeList")->with('ingredientAllowSuccess', "L'ingrédient a été modéré");
            }

            // Si erreur dans la transaction
            catch (QueryException $e) {
                DB::rollback();

                return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
            }
        }
    }

    public function show(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (! $user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        $response = [];

        return view('newingredient', $response);
    }

    public function propose(Request $request)
    {

        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (! $user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }

        // Validation du formulaire avec les différentes règles
        $request->validate([
            'ingredient' => ['string', 'required', 'min:2', 'max:255'],
            'rulescheck' => ['accepted', 'required'],
        ]);
        if ($request->rulescheck !== 'true') {
            return back()->withInput()->withErrors(['rulesError' => 'Veuillez accepter les règles pour valider la proposition']);
        }

        // Création d'un nouvel ingredient
        $newIngredient = new Ingredient;
        $newIngredient->user_id = $user->id;
        $newIngredient->name = $request->ingredient;
        $newIngredient->icon = null;
        $newIngredient->is_accepted = null;
        $newIngredient->save();

        return redirect('/my-recipes')->with('ingredientProposeSuccess', "L'ingrédient a été proposé, vous recevrez un mail de modération.");
    }
}
