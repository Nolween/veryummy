<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeIngredients extends Model
{
    use HasFactory;

    protected $fillable = ['recipe_id', 'unit_id', 'ingredient_id', 'quantity', 'order'];

    /**
     * A quelle recette appartient cette ligne
     *
     * @return void
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * A quelle ingredient appartient cette ligne
     *
     * @return void
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
