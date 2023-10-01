<?php

namespace App\Http\Controllers;

use App\Enums\Units;
use App\Http\Requests\Recipe\RecipeAdminIndexRequest;
use App\Http\Requests\Recipe\RecipeAllowRequest;
use App\Http\Requests\Recipe\RecipeCommentRequest;
use App\Http\Requests\Recipe\RecipeEditRequest;
use App\Http\Requests\Recipe\RecipeEmptyOpinionRequest;
use App\Http\Requests\Recipe\RecipeExplorationRequest;
use App\Http\Requests\Recipe\RecipeNoteBookIndexRequest;
use App\Http\Requests\Recipe\RecipeShowRequest;
use App\Http\Requests\Recipe\RecipeStatusRequest;
use App\Http\Requests\Recipe\RecipeStoreRequest;
use App\Http\Requests\Recipe\RecipeUpdateRequest;
use App\Http\Requests\Recipe\RecipeUserIndexRequest;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Models\User;
use App\Repositories\RecipeRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
            'units'       => Units::allValues(),
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
            return back()->withErrors(['newError' => 'Erreur dans la création de la recette'])->withInput();
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
            'units'           => Units::allValues(),
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

        if (! $user) {
            abort(403, 'Unauthorized action.');
        }
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
     */
    public function show(RecipeShowRequest $request, int $id): View
    {
        $user   = Auth::user();
        $userId = $user->id ?? null;
        $recipe = $this->recipeRepository->showRecipe($id);

        $response = [
            'recipe'      => $recipe,
            'ingredients' => Recipe::findOrFail($id)->ingredients()->get(),
            'steps'       => Recipe::findOrFail($id)->steps()->get(),
            'comments'    => Recipe::findOrFail($id)->comments()->where('user_id', '!=', $userId)->get(),
            'userId'      => $userId,
            'opinion'     => ! empty($user) ? RecipeOpinion::whereBelongsTo($user)->where('recipe_id', $id)->first(
            ) : [],
            'type' => RecipeType::where('id', $recipe->recipe_type_id)->firstOrFail()->name,
        ];

        return view('recipeshow', $response);
    }

    /**
     * @details Poster / Créer un commentaire sur la recette
     */
    public function comment(RecipeCommentRequest $request, Recipe $recipe): RedirectResponse
    {
        if ($this->recipeRepository->commentRecipe($request, $recipe)) {
            return back()->with('success', 'Commentaire effectué');
        } else {
            return back()->withErrors(['error' => 'Erreur dans la mise à jour du statut']);
        }
    }

    /**
     * @details Supprimer l'opinion et la note de l'utilisateur
     */
    public function emptyOpinion(RecipeEmptyOpinionRequest $request, Recipe $recipe): RedirectResponse
    {
        if ($this->recipeRepository->emptyOpinionRecipe($recipe)) {
            return back()->with('success', 'Commentaire supprimé');
        } else {
            return back()->withErrors(['error' => 'Erreur dans la suppression du commentaire']);
        }
    }

    /**
     * @details Listes des recettes de l'utilisateur
     */
    public function userIndex(RecipeUserIndexRequest $request): View|RedirectResponse
    {
        $response = [];

        $recipesQuery = $this->recipeRepository->userIndex($request);

        // Création d'un type temporaire tous
        $allTypes = new RecipeType(['name' => 'Tous', 'id' => 0]);

        $response = [
            'recipes' => $recipesQuery->paginate(20),
            'total'   => $recipesQuery->count(),
            'types'   => RecipeType::all()->prepend($allTypes),
            'diet'    => $request->diet   ?? null,
            'search'  => $request->name   ?? null,
            'typeId'  => $request->typeId ?? null,
        ];

        return view('myrecipes', $response);
    }

    /**
     * @details Listes des recettes favories de l'utilisateur
     */
    public function noteBookIndex(RecipeNoteBookIndexRequest $request): View|RedirectResponse
    {
        if (Auth::user() === null) {
            abort(403);
        }

        $recipesQuery = $this->recipeRepository->noteBookIndex($request);

        // Création d'un type temporaire tous
        $allTypes = new RecipeType(['name' => 'Tous', 'id' => 0]);

        $response = [
            'recipes' => $recipesQuery->paginate(20),
            'total'   => $recipesQuery->count(),
            'types'   => RecipeType::all()->prepend($allTypes),
            'search'  => $request->name   ?? null,
            'diet'    => $request->diet   ?? null,
            'typeId'  => $request->typeId ?? null,
        ];

        return view('mynotebook', $response);
    }
}
