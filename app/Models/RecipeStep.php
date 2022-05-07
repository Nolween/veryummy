<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeStep extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = ['recipe_id', 'order', 'description'];

    /**
     * La recette à laquelle l'étape appartient
     *
     * @return void
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
