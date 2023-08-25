<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\ListIngredientRequest;
use App\Mail\AcceptedIngredient;
use App\Mail\RefusedIngredient;
use App\Models\Ingredient;
use App\Models\User;
use App\Repositories\IngredientRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IngredientController extends Controller
{

    public function __construct(IngredientRepository $ingredientRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * @details Liste des ingrédients
     */
    public function index(ListIngredientRequest $request, int $type): View|RedirectResponse
    {
        $response = [];
        $response['typeList'] = $type;

        $type = match ($type) {
            1       => true,
            2       => false,
            default => null,
        };

        $response['ingredients'] = $this->ingredientRepository->getIngredients($type, $request->search);
        $response['search'] = $request->search ?? null;

        return view('adminingredientslist', $response);
    }

    /**
     * Refuser un ingrédient
     */
    public function deny(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role !== 'admin' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'ingredientid' => ['integer', 'required'],
            'deny'         => ['accepted', 'required'],
            'typeList'     => ['integer', 'required'],
            'denymessage'  => ['string', 'required', 'min:2'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', (int)$request->ingredientid)->with('user')->first();
            // Si pas d'ingrédient trouvé, erreur
            if (!$ingredient) {
                return back()->withErrors(['ingredientAllowError' => 'Aucun ingrédient trouvé']);
            }
            $authorMail = null;
            if ($ingredient->user) {
                $authorMail = $ingredient->user->email;
            }
            $ingredient->is_accepted = false;
            // Si l'ingrédient est accepté, il passe sur le compte principal, en cas de suppression de compte du demandeur
            $ingredient->user_id = 1;
            $ingredient->save();

            // Envoi de mail à la personne ayant proposé l'ingrédient
            $informations = [
                'ingredient' => $ingredient->name,
                'url'        => URL::to('/'),
                'message'    => $request->denymessage,
            ];
            // Si la modération était en cours
            if (!empty($authorMail) && $request->typeList == 0) {
                Mail::to($authorMail)->send(new RefusedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/ingredients/list/$request->typeList")->with(
                'ingredientAllowSuccess',
                "L'ingrédient a été modéré"
            );
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
        }
    }

    /**
     * Accepter un ingrédient
     */
    public function allow(Request $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->role !== 'admin' || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        // Validation du formulaire avec les différentes règles
        $request->validate([
            'ingredientid' => ['integer', 'required'],
            'allow'        => ['accepted', 'required'],
            'finalname'    => ['string', 'required', 'min:2', 'max:255'],
            'typeList'     => ['integer', 'required'],
            'vegetarian'   => ['boolean', 'nullable'],
            'vegan'        => ['boolean', 'nullable'],
            'glutenfree'   => ['boolean', 'nullable'],
            'halal'        => ['boolean', 'nullable'],
            'kosher'       => ['boolean', 'nullable'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', (int)$request->ingredientid)->with('user')->first();
            // Si pas d'ingrédient trouvé, erreur
            if (!$ingredient) {
                return back()->withErrors(['ingredientAllowError' => 'Aucun ingrédient trouvé']);
            }
            $authorMail = null;
            if ($ingredient->user) {
                $authorMail = $ingredient->user->email;
            }
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
            if ($authorMail && $request->typeList == 0) {
                Mail::to($authorMail)->send(new AcceptedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();

            return redirect("/admin/ingredients/list/$request->typeList")->with(
                'ingredientAllowSuccess',
                "L'ingrédient a été modéré"
            );
        } // Si erreur dans la transaction
        catch (QueryException $e) {
            DB::rollback();

            return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
        }
    }

    /**
     * Affichage de la page de proposition d'un ingrédient
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }
        $response = [];

        return view('newingredient', $response);
    }

    /**
     * Proposition d'un ingrédient
     *
     * @return RedirectResponse
     */
    public function propose(Request $request)
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
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
            return back()->withInput()->withErrors(
                ['rulesError' => 'Veuillez accepter les règles pour valider la proposition']
            );
        }

        // Création d'un nouvel ingredient
        $newIngredient = new Ingredient;
        $newIngredient->user_id = $user->id;
        $newIngredient->name = $request->ingredient;
        $newIngredient->icon = null;
        $newIngredient->is_accepted = null;
        $newIngredient->save();

        return redirect('/my-recipes')->with(
            'ingredientProposeSuccess',
            "L'ingrédient a été proposé, vous recevrez un mail de modération."
        );
    }
}
