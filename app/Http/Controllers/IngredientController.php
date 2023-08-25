<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\AllowIngredientRequest;
use App\Http\Requests\Ingredient\DenyIngredientRequest;
use App\Http\Requests\Ingredient\ListIngredientRequest;
use App\Http\Requests\Ingredient\CreateIngredientRequest;
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
     * @details Refuser un ingrédient
     */
    public function deny(DenyIngredientRequest $request): RedirectResponse
    {
        // Intéraction avec la DB pour refuser l'ingrédient
        if ($this->ingredientRepository->denyIngredient($request)) {
            return redirect("/admin/ingredients/index/$request->typeList")->with(
                'ingredientAllowSuccess',
                "L'ingrédient a été modéré"
            );
        } else {
            return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
        }
    }

    /**
     * @details Accepter un ingrédient
     */
    public function allow(AllowIngredientRequest $request): RedirectResponse
    {
        if ($this->ingredientRepository->allowIngredient($request)) {
            return redirect("/admin/ingredients/index/$request->typeList")->with(
                'ingredientAllowSuccess',
                "L'ingrédient a été modéré"
            );
        } else {
            return back()->withErrors(['ingredientAllowError' => "Erreur dans la modération de l'ingrédient"]);
        }
    }


    /**
     * @details Affichage de la page de proposition d'un ingrédient
     */
    public function create(CreateIngredientRequest $request): View|RedirectResponse
    {
        return view('newingredient');
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
