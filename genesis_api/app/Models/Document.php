<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['user_id', 'nom_fichier', 'chemin_fichier', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec la table consultations
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

}
