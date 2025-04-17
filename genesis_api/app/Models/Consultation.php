<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'rendez_vous_id',
        'diagnostic',
        'note',
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }
}

