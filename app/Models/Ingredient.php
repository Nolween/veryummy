<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property ?string $icon
 * @property ?bool $is_accepted
 * @property ?array $diets
 * @property-read User $user
 */
class Ingredient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'is_accepted',
        'diets',
    ];

    protected $casts = [
        'diets' => 'array',
    ];

    /**
     * Get the user that owns the ingredient.
     *
     * @return BelongsTo<User,Ingredient>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
