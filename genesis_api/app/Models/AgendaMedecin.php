<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaMedecin extends Model
{
    protected $fillable = [
        'medecin_id',
        'jour',
        'heure_debut',
        'heure_fin',
        'bloque',
    ];

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}

