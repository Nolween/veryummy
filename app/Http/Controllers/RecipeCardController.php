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
