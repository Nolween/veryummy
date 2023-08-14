<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    // Utilisation de constantes pour faciliter la lecture du code

    public const IS_ADMIN = 1;

    public const IS_USER = 2;

    // Pas besoin des created_at et updated_at
    public $timestamps = false;

    /**
     * Get the users that owns the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
