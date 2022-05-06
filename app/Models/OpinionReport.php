<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpinionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'opinion_reports',
        'user_id',
        'opinion_id'
    ];

    /**
     * Le commentaire recette auquel il appartient
     *
     * @return void
     */
    public function opinion()
    {
        return $this->belongsTo(RecipeOpinion::class);
    }

    /**
     * L'utilisateur qui a signalé le commentaire
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
