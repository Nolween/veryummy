<?php

namespace App\Repositories;

use App\Mail\RefusedIngredient;
use App\Models\Ingredient;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class IngredientRepository
{

    /**
     * @details Récupération des ingrédients
     * @param int $type
     * @param string|null $search
     * @return LengthAwarePaginator
     */
    public function getIngredients(?int $type, ?string $search): LengthAwarePaginator
    {
        // Récupération des ingrédients
        $ingredients = Ingredient::select('*');
        // Si on a quelque chose dans la recherche
        if (!empty($search)) {
            $ingredients->where('name', 'like', "%{$search}%");
        }
        // Si ingédients autorisés ou refusés seulement
        if ($type !== null) {
            $ingredients->where('is_accepted', $type);
        }

        return $ingredients->with('user')->paginate(20);
    }

    /**
     * @details Refuser un ingrédient
     */
    public function denyIngredient(int $ingredientId, string $denyMessage, int $typeList): bool
    {
        // Transaction pour rollback si erreur
        DB::beginTransaction();
        try {
            // Récupération de l'ingrédient par son Id
            $ingredient = Ingredient::where('id', $ingredientId)->with('user')->firstOrFail();

            $authorMail = $ingredient->user->email;
            $ingredient->is_accepted = false;
            $ingredient->save();

            if (!empty($authorMail) && $typeList == 0) {
                // Envoi de mail à la personne ayant proposé l'ingrédient
                $informations = [
                    'ingredient' => $ingredient->name,
                    'url'        => URL::to('/'),
                    'message'    => $denyMessage,
                ];
                Mail::to($authorMail)->send(new RefusedIngredient($informations));
            }

            // Validation de la transaction
            DB::commit();

            return true;
        } // Si erreur dans la transaction
        catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }
}
