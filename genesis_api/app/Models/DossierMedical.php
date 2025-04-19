<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    protected $fillable = ['patient_id', 'consultation_id', 'description'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}

