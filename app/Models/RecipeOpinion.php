<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeOpinion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_id',
        'comment',
        'score',
        'is_reported',
        'is_favorite',
    ];

    /**
     * La recette auquel le commentaire appartient
     *
     * @return BelongsTo<Recipe>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * L'utilisateur auquel le commentaire appartient
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les signalements concernant le commentaire d'un utilisateur
     *
     * @return HasMany<OpinionReport>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(OpinionReport::class, 'opinion_id');
    }
}
