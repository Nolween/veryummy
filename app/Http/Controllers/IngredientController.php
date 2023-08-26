<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\AllowIngredientRequest;
use App\Http\Requests\Ingredient\DenyIngredientRequest;
use App\Http\Requests\Ingredient\ListIngredientRequest;
use App\Http\Requests\Ingredient\CreateIngredientRequest;
use App\Http\Requests\Ingredient\StoreIngredientRequest;
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
     * @details Proposition d'un ingrédient
     */
    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        if ($this->ingredientRepository->storeIngredient($request)) {
            return redirect('/my-recipes')->with(
                'ingredientProposeSuccess',
                "L'ingrédient a été proposé, vous recevrez un mail de modération."
            );
        } else {
            return back()->withErrors(['ingredientProposeError' => "Erreur dans la proposition de l'ingrédient"]);
        }
    }
}
