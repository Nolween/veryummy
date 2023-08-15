<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpinionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'opinion_reports',
        'user_id',
        'opinion_id',
    ];

    /**
     * Le commentaire recette auquel il appartient
     *
     * @return BelongsTo<RecipeOpinion, RecipeOpinion>
     */
    public function opinion(): BelongsTo
    {
        return $this->belongsTo(RecipeOpinion::class);
    }

    /**
     * L'utilisateur qui a signalé le commentaire
     *
     * @return BelongsTo<User, RecipeOpinion>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
