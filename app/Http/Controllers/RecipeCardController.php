<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Rules\Score;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RecipeCardController extends Controller
{

    /**
     * Mettre en favori / Signaler une recette
     */
    public function status(Request $request, int $recipeId): RedirectResponse
    {
        // Récupération de l'utilisateur
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['badUser' => 'Utilisateur non trouvé']);
        }

        // La recette existe t-elle?
        $recipe = Recipe::findOrFail($recipeId);
        // Validation du formulaire
        $request->validate([
            'is_favorite' => ['boolean', 'nullable'],
            'is_reported' => ['boolean', 'nullable'],
        ]);

        RecipeOpinion::updateOrCreate(
            ['user_id' => $user->id, 'recipe_id' => $recipeId],
            ['is_favorite' => (bool)$request->is_favorite, 'is_reported' => (bool)$request->is_reported]
        );

        return redirect('/recipe/show/' . $recipeId)->with(
            'statusSuccess',
            (bool)$request->is_favorite ? 'Recette mise en favori' : 'Recette signalée'
        );
    }

    /**
     * Poster / Créer un commentaire sur la recette
     */
    public function comment(Request $request, int $recipeId): RedirectResponse
    {
        // Quelle est l'ID de la recette?
        $recipe = Recipe::findOrFail($recipeId);
        // Récupération de l'utilisateur
        $user = Auth::user();

        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();

            return redirect('/')->withErrors(['error' => "Pas d'utilisateur"]);
        }
        // Validation des données
        $request->validate([
            'score' => [new Score, 'required', 'max:5', 'min:1'],
            // Le socre doit passer la règle Score de App/Rules/Score
            'comment' => ['string', 'required', 'min:2', 'max:65535'],
        ]);

        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Calcul de la nouvelle note moyenne de la recette
            $recipe = Recipe::find($recipeId);
            // Si pas de recette trouvée, erreur
            if (!$recipe) {
                return back()->withErrors(['recipeError' => 'Recette inexistante']);
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
        } // Si erreur dans la transaction
        catch (ItemNotFoundException $e) {
            DB::rollback();

            return back()->withErrors(['error' => 'Recette introuvable']);
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return back()->withErrors(['error' => 'Erreur dans la mise à jour du statut']);
        }
    }

    /**
     * Supprimer l'opinion et la note de l'utilisateur
     */
    public function emptyOpinion(Request $request, int $recipeId): RedirectResponse
    {
        // Quelle est l'ID de la recette?
        $recipe = Recipe::findOrFail($recipeId);
        // Récupération de l'utilisateur
        $user = Auth::user();
        // Si pas d'utilisateur
        if (!$user || $user->is_banned == true) {
            // Déconnexion de l'utilisateur
            Auth::logout();
            return redirect('/')->withErrors(['badUser' => 'Utilisateur non reconnu']);
        }
        // Trouver l'opinion de la recette par l'utilisateur
        // dd($recipe->opinions()->where('user_id', $user->id)->firstOrFail());
        $recipeOpinion = $recipe->opinions()->where('user_id', $user->id)->firstOrFail();
        // Réinitialisation du commentaire et de la note de l'avis sur la recette
        $recipeOpinion->score = null;
        $recipeOpinion->comment = null;
        $recipeOpinion->save();
        // Définition de la nouvelle moyenne
        $average = RecipeOpinion::whereBelongsTo($recipe)->avg('score');
        $recipe->score = $average;
        $recipe->save();

        return redirect("/recipe/show/$recipeId")->with('success', 'Commentaire supprimé');
    }
}
