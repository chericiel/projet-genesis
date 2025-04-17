<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'date_rdv',
        'heure_rdv',
        'statut'
    ];

    // Relation vers Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relation vers Médecin
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}
