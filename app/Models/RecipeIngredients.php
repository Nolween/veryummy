<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredients extends Model
{
    use HasFactory;

    // Pas besoin des created_at et updated_at
    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'unit',
        'ingredient_id',
        'quantity',
        'order',
    ];

    /**
     * A quelle recette appartient cette ligne
     *
     * @return BelongsTo<Recipe, RecipeIngredients>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * A quelle ingredient appartient cette ligne
     *
     * @return BelongsTo<Ingredient, RecipeIngredients>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

}
