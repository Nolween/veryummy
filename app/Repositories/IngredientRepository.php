<?php

namespace App\Repositories;

use App\Models\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

class IngredientRepository {

    /**
     * @details Récupération des ingrédients
     * @param int $type
     * @param string|null $search
     * @return LengthAwarePaginator
     */
    public function getIngredients(?int $type, ?string $search) : LengthAwarePaginator
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
}
