<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'is_accepted',
        'vegan_compatible',
        'vegetarian_compatible',
        'gluten_free_compatible',
        'halal_compatible',
        'kosher_compatible',
    ];

    /**
     * Get the user that owns the ingredient.
     * @return BelongsTo<User,Ingredient>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
