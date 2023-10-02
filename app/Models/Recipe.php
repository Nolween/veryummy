<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $user_id
 * @property string $recipe_type
 * @property string $name
 * @property int $cooking_time
 * @property int $making_time
 * @property int servings
 * @property double $score
 * @property string $image
 * @property bool $is_accepted
 * @property array $diets
 * @property-read User $user
 * @property-read RecipeStep[] $steps
 * @property-read RecipeOpinion[] $comments
 * @property-read RecipeIngredients[] $ingredients
 * @property-read RecipeOpinion[] $opinions
 * @property-read RecipeOpinion $opinion
 */
class Recipe extends Model
{
    use HasFactory;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'recipe_type',
        'name',
        'cooking_time',
        'making_time',
        'score',
        'image',
        'is_accepted',
        'diets',
    ];

    protected $casts = [
        'diets' => 'array',
    ];

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
        return $this->hasMany(RecipeIngredients::class)->with('ingredient')->orderBy('order');
    }

    /**
     * Indique les opinions de la recette
     *
     * @return HasMany<RecipeOpinion>
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
                                                               'id'         => 'max',
                                                           ], function ($query) {
            $query->where('user_id', '=', Auth::id());
        });
        // return $this->hasMany(RecipeOpinion::class)->with('user')->where('user_id', );
    }
}
