<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeOpinion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_id',
        'comment',
        'score',
        'is_reported',
        'is_favorite'
    ];

    /**
     * La recette auquel le commentaire appartient
     *
     * @return void
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * L'utilisateur auquel le commentaire appartient
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les signalements concernant le commentaire d'un utilisateur
     *
     * @return void
     */
    public function reports()
    {
        return $this->hasMany(OpinionReport::class, 'opinion_id');
    }


}
