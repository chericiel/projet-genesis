<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    protected $fillable = [
        'user_id', 
        'specialite', 
        'experience', 
        'honoraires'
    ];

    // On définis la relation avec User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // On définis la relation avec RendezVous
    public function rendezVous()
    {   
        return $this->hasMany(RendezVous::class);
    }
}
