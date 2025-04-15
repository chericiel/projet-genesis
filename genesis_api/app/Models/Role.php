<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['libelle'];

    // 🔁 Un rôle peut être associé à plusieurs utilisateurs
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
