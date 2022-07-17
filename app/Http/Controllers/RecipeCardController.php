<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Rules\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;

class RecipeCardController extends Controller
{

    /**
     * Page d'accueil
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $response = [];
        // Récupération de la recette grâce à son od
        $response['recipe'] = Recipe::select('id', 'user_id', 'name', 'servings', 'cooking_time as cookingTime', 'making_time as makingTime', 'image', 'score', 'recipe_type_id', 'vegan_compatible', 'vegetarian_compatible', 'gluten_free_compatible', 'halal_compatible', 'kosher_compatible')
            ->withCount('steps') // Nombre d'étapes possède la recette
            ->withCount('ingredients') // Nombre d'ingrédients dans la recette 
            ->findOrFail($id);
        $response['ingredients'] =  Recipe::findOrFail($id)->ingredients;
        $response['steps'] =  Recipe::findOrFail($id)->steps;
        // Si l'utilisateur est connecté 
        $user = Auth::user();
        $response['userId'] = $user->id ?? null;
        // Tous les avis de la recette sauf celui de l'utilisateur connecté
        $response['comments'] =  Recipe::findOrFail($id)->comments->where('user_id', '!=', $response['userId']);
        // Si utilisateur connecté, récupération de son avis sur la recette (+ fav + report)
        $response['opinion'] = !empty($user) ? RecipeOpinion::whereBelongsTo($user)->where('recipe_id', $id)->first() : [];

        $response['type'] = RecipeType::where('id', $response['recipe']->recipe_type_id)->first()->name;
        return view('recipeshow', $response);
    }

    /**
     * Mettre en favori / Signaler une recette
     *
     * @param Request $request
     * @return void
     */
    public function status(Request $request)
    {
        // Quelle est l'ID de la recette?
        $recipeId = $request->route('id');
        // Récupération de l'utilisateur
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/")->withErrors(['statusError', 'Utilisateur non trouvée']);
        }

        // La recette existe t-elle?
        $recipe = Recipe::where('id', $recipeId)->first();
        if(!$recipe) {
            return redirect("/recipe/show/$recipeId")->withErrors(['statusError', 'Recette non trouvée']);
        }
        // Validation du formulaire
        $request->validate([
            'is_favorite' => ['boolean', 'nullable'],
            'is_reported' => ['boolean', 'nullable'],
        ]);

        RecipeOpinion::updateOrCreate(
            ['user_id' => $user->id, 'recipe_id' => $recipeId],
            ['is_favorite' => (bool)$request->is_favorite, 'is_reported' => (bool)$request->is_reported]
        );

        return redirect("/recipe/show/$recipeId")->with('statusSuccess', (bool)$request->is_favorite ? 'Recette mise en favori' : 'Recette signalée');
    }

    /**
     * Poster / Créer un commentaire sur la recette
     *
     * @param Request $request
     * @return void
     */
    public function comment(Request $request)
    {
        // Quelle est l'ID de la recette?
        $recipeId = $request->route('id');
        // Récupération de l'utilisateur
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/")->withErrors(['error' => "Pas d'utilisateur"]);
        }
        // Validation des données
        $request->validate([
            'score' => [new Score, 'required', 'max:5', 'min:1'], // Le socre doit passer la règle Score de App/Rules/Score
            'comment' => ['string', 'required', 'min:2', 'max:65535'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Calcul de la nouvelle note moyenne de la recette
            $recipe = Recipe::find($recipeId);
            // Si pas de recette trouvée, erreur
            if (!$recipe) {
                throw new ItemNotFoundException();
            }

            RecipeOpinion::updateOrCreate(
                ['user_id' => $user->id, 'recipe_id' => $recipeId],
                ['score' => $request->score, 'comment' => $request->comment]
            );


            $average = RecipeOpinion::whereBelongsTo($recipe)->avg('score');
            $recipe->score = $average;
            $recipe->save();

            // Validation de la transaction
            DB::commit();
            return back()->with('success', 'Commentaire effectué');
        }

        // Si erreur dans la transaction
        catch (ItemNotFoundException $e) {
            DB::rollback();
            return back()->withErrors(['error', 'Recette introuvable']);
        }
        // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();
            return back()->withErrors(['error', 'Erreur dans la mise à jour du statut']);
        }
    }

    /**
     * Supprimer l'opinion et la note de l'utilisateur
     *
     * @param Request $request
     * @return void
     */
    public function emptyOpinion(Request $request)
    {
        // Quelle est l'ID de la recette?
        $recipeId = $request->route('id');
        $recipe = Recipe::findOrFail($recipeId);
        // Récupération de l'utilisateur
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect("/")->withErrors(['badUser' => "Utilisateur non reconnu"]);
        }
        // Trouver l'opinion de la recette par l'utilisateur
        $recipeOpinion = RecipeOpinion::whereBelongsTo($recipe)->whereBelongsTo($user)->firstOrFail();
        if ($recipeOpinion) {
            // Réinitialisation du commentaire et de la note de l'avis sur la recette
            $recipeOpinion->score = null;
            $recipeOpinion->comment = null;
            $recipeOpinion->save();
            // Définition de la nouvelle moyenne
            $average = RecipeOpinion::whereBelongsTo($recipe)->avg('score');
            $recipe->score = $average;
            $recipe->save();
        }

        return redirect("/recipe/show/$recipeId")->withErrors(['success', 'Commentaire supprimé']);
    }
}
