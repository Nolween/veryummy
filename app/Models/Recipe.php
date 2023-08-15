<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_type_id',
        'name',
        'cooking_time',
        'making_time',
        'score',
        'image',
        'is_accepted',
        'vegan_compatible',
        'vegetarian_compatible',
        'gluten_free_compatible',
        'halal_compatible',
        'kosher_compatible',
    ];

    /**
     * Indique à quel type de recette elle appartient
     *
     * @return BelongsTo<RecipeType, Recipe>
     */
    public function recipeType(): BelongsTo
    {
        return $this->belongsTo(RecipeType::class);
    }

    /**
     * Indique à quel utilisateur elle appartient
     *
     * @return BelongsTo<User, Recipe>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Indique les étapes de la recette
     *
     * @return HasMany<RecipeStep>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('order');
    }

    /**
     * Indique les avis de la recette
     *
     * @return HasMany<RecipeOpinion>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(RecipeOpinion::class);
    }

    /**
     * Indique les ingrédients de la recette et son type d'unité
     *
     * @return HasMany<RecipeIngredients>
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredients::class)->with('ingredient')->with('unit')->orderBy('order');
    }

    /**
     * Indique les opinions de la recette
     *
     * @return HasMany
     */
    public function opinions(): HasMany
    {
        return $this->hasMany(RecipeOpinion::class)->with('user');
    }

    /**
     * Indique l'avis de l'utilisateur sur la recette
     *
     * @return HasOne<RecipeOpinion>
     */
    public function opinion(): HasOne
    {
        return $this->hasOne(RecipeOpinion::class)->ofMany([
            'updated_at' => 'max',
            'id' => 'max',
        ], function ($query) {
            $query->where('user_id', '=', Auth::id());
        });
        // return $this->hasMany(RecipeOpinion::class)->with('user')->where('user_id', );
    }
}
