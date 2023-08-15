<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    /**
     * Tableau des champs remplissables
     *
     * @var array<string>
     */
    protected $fillable = ['name'];

    // Pas besoin des created_at et updated_at
    public $timestamps = false;
}
