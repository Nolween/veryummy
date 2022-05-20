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

class IngredientController extends Controller
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

        // Récupération des ingrédients
        $response['ingredients'] = Ingredient::where('is_accepted', $type)->with('user')->paginate(20);
        $response['typeList'] = (int)$type;
        // dd( $response['ingredients']);

        return view('adminingredientslist', $response);
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
            'ingredientid' => ['integer', 'required'],
            'allow' => ['boolean', 'required'],
            'typeList' => ['integer', 'required'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', (int)$request->ingredientid)->with('user')->first();
            // Si pas d'ingrédient trouvé, erreur
            if (!$ingredient) {
                return back()->with('ingredientAllowError', 'Aucun ingrédient trouvé');
            }
            $author = $ingredient->user->id;
            $ingredient->is_accepted = $request->allow;
            // Si l'ingrédient est accepté, il passe sur le compte principal, en cas de suppression de compte du demandeur
            $ingredient->user_id = 1;
            $ingredient->save();

            // Envoi de mail à la personne ayant proposé l'ingrédient
            $informations = ['ingredient' => $ingredient->name, 'url' => URL::to('/')];
            // dd($informations);
            if ($request->allow == 1) {
                Mail::to($user->email)->send(new AcceptedIngredient($informations));
            } else {
                Mail::to($user->email)->send(new RefusedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();
            return redirect("/admin/ingredients/list/$request->typeList")->with('ingredientAllowSuccess', "L'ingrédient a été modéré");
        }

        // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();
            return back()->with('ingredientAllowError', "Erreur dans la modération de l'ingrédient");
        }
    }
}
