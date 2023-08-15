<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_banned',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Indique les recettes de l'utilisateur
     *
     * @return HasMany<Recipe>
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Indique les ingredients de l'utilisateur
     *
     * @return HasMany<Ingredient>
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /**
     * Indique le rôle de l'utilisateur
     *
     * @return BelongsTo<Role, User>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Indique les signalements qu'a fait l'utilisateur
     *
     * @return HasMany<OpinionReport>
     */
    public function reportedOpinions(): HasMany
    {
        return $this->hasMany(OpinionReport::class);
    }

    /**
     * Indique les commentaires / reports de l'utilisateur
     *
     * @return HasMany<RecipeOpinion>
     */
    public function opinions(): HasMany
    {
        return $this->hasMany(RecipeOpinion::class);
    }

    /**
     * Indique les commentaires de l'utilisateur signalés par les autres
     *
     * @return HasManyThrough<OpinionReport>
     */
    public function reportedOpinionsByOther(): HasManyThrough
    {
        return $this->hasManyThrough(
            OpinionReport::class,
            RecipeOpinion::class,
            'user_id',
            'opinion_id',
            'id',
            'id'
        );

    }
}
