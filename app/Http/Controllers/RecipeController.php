<?php

namespace App\Http\Controllers;

use App\Helpers\ImageTransformation;
use App\Http\Requests\Recipe\RecipeAdminIndexRequest;
use App\Http\Requests\Recipe\RecipeAllowRequest;
use App\Http\Requests\Recipe\RecipeCommentRequest;
use App\Http\Requests\Recipe\RecipeEditRequest;
use App\Http\Requests\Recipe\RecipeExplorationRequest;
use App\Http\Requests\Recipe\RecipeShowRequest;
use App\Http\Requests\Recipe\RecipeStatusRequest;
use App\Http\Requests\Recipe\RecipeStoreRequest;
use App\Http\Requests\Recipe\RecipeUpdateRequest;
use App\Mail\RefusedRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\RecipeStep;
use App\Models\RecipeType;
use App\Models\Unit;
use App\Models\User;
use App\Repositories\RecipeRepository;
use App\Rules\DietExists;
use App\Rules\Score;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;
use Illuminate\View\View;

use function imageavif;

class RecipeController extends Controller
{

    private RecipeRepository $recipeRepository;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @details Page d'accueil
     */
    public function welcomeIndex(): View
    {
        $response = $this->recipeRepository->getWelcomeIndex();

        return view('welcome', $response);
    }


    /**
     * @details Listes des recettes d'exploration
     */
    public function explorationIndex(RecipeExplorationRequest $request): View
    {
        $response = $this->recipeRepository->getExplorationIndex($request);


        return view('exploration', $response);
    }


    /**
     * @details Recettes dans l'administation
     */
    public function adminIndex(int $type, RecipeAdminIndexRequest $request): View|RedirectResponse
    {
        $response = $this->recipeRepository->getAdminIndex($request, $type);

        return view('adminrecipeslist', $response);
    }

    /**
     * @details  Modérer une recette
     */
    public function moderate(RecipeAllowRequest $request): RedirectResponse
    {
        if ($this->recipeRepository->moderateRecipe($request)) {
            return redirect("/admin/recipes/index/$request->typeList")->with(
                'recipeAllowSuccess',
                'La recette a été modérée'
            );
        } else {
            return back()->withErrors(['recipeAllowError' => 'Erreur dans la modération de la recette']);
        }
    }

    /**
     * @details Aimer ou signaler une recette
     */
    public function status(RecipeStatusRequest $request): RedirectResponse
    {
        if ($this->recipeRepository->updateStatus($request)) {
            // Définition du message de retour
            if ($request->is_reported == null) {
                $message = $request->is_favorite == 1 ? 'La recette a été ajoutée à vos favoris' : 'La recette a été retirée de vos favoris';
            } elseif ($request->is_favorite == null) {
                $message = $request->is_reported == 1 ? 'La recette a été signalée' : 'La recette a été retirée des signalements';
            } else {
                $message = 'Erreur inconnue';
            }
            return back()->with('statusSuccess', $message);
        } else {
            return back()->withErrors(['statusError' => 'Erreur dans la mise à jour du statut']);
        }
    }


    /**
     * @details Page de nouvelle recette
     */
    public function create(): View|RedirectResponse
    {
        $response = [
            'ingredients' => Ingredient::pluck('name', 'id'),
            'units'       => Unit::all(),
            'types'       => RecipeType::all(),
        ];

        return view('recipenew', $response);
    }

    /**
     * @details Création de nouvelle recette
     */
    public function store(RecipeStoreRequest $request): RedirectResponse
    {
        if ($this->recipeRepository->storeRecipe($request)) {
            return redirect('/my-recipes')->with('newSuccess', 'Recette crée avec succès!');
        } else {
            return back()->withErrors(['newError' => 'Erreur dans la création de la recette']);
        }
    }

    /**
     * @details Page de vue de recette
     */
    public function edit(RecipeEditRequest $request, int $id): View|RedirectResponse
    {
        $user = Auth::user();

        // Récupération de la recette
        $recipe = Recipe::where('id', $id)->with('ingredients')->with('steps')->firstOrFail();

        $response = [
            'ingredientsList' => Ingredient::pluck('name', 'id'),
            'units'           => Unit::all(),
            'types'           => RecipeType::all(),
            'recipe'          => $recipe,
        ];

        return view('recipeedit', $response);
    }

    /**
     * Modification de recette
     */
    public function update(RecipeUpdateRequest $request): RedirectResponse
    {
        // Récupération des infos de l'utilisateur connecté
        $user = Auth::user();

        // La recette existe t-elle et appartient-elle à l'utilisateur?
        $recipe = Recipe::findOrFail($request->recipeid);
        if ($recipe->user_id !== $user->id && $user->role !== User::ROLE_ADMIN) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->recipeRepository->updateRecipe($request, $recipe)) {
            return redirect('/my-recipes')->with('updateSuccess', 'Recette mise à jour avec succès!');
        } else {
            return redirect('/recipe/new')->withErrors(['updaterror' => 'Erreur dans la mise à jour de la recette']);
        }
    }


    /**
     * @details Page d'accueil
     *
     */
    public function show(RecipeShowRequest $request, int $id): View
    {
        $user = Auth::user();
        $userId = $user->id ?? null;
        $recipe = $this->recipeRepository->showRecipe($id);

        $response = [
            'recipe'      => $recipe,
            'ingredients' => Recipe::findOrFail($id)->ingredients()->get(),
            'steps'       => Recipe::findOrFail($id)->steps()->get(),
            'comments'    => Recipe::findOrFail($id)->comments()->where('user_id', '!=', $userId)->get(),
            'userId'      => $userId,
            'opinion'     => !empty($user) ? RecipeOpinion::whereBelongsTo($user)->where('recipe_id', $id)->first(
            ) : [],
            'type'        => RecipeType::where('id', $recipe->recipe_type_id)->firstOrFail()->name,
        ];

        return view('recipeshow', $response);
    }


    /**
     * @details Poster / Créer un commentaire sur la recette
     */
    public function comment(RecipeCommentRequest $request, Recipe $recipe): RedirectResponse
    {

        if($this->recipeRepository->commentRecipe($request, $recipe)) {
            return back()->with('success', 'Commentaire effectué');
        } else {
            return back()->withErrors(['error' => 'Erreur dans la mise à jour du statut']);
        }

    }




}
