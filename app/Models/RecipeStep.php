<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeStep extends Model
{
    use HasFactory;

    // Pas besoin des created_at et updated_at
    public $timestamps = false;

    protected $fillable = ['recipe_id', 'order', 'description'];

    /**
     * La recette à laquelle l'étape appartient
     *
     * @return BelongsTo<Recipe, RecipeStep>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
