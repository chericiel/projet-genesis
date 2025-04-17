<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'adresse',
        'telephone',
        'role_id'
    ];

    //  Un utilisateur appartient à un rôle
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //  Un utilisateur a une seule ligne de connexion
    public function connection()
    {
        return $this->hasOne(Connection::class);
    }

    // Relation avec le patient
    public function patient()
    {
        return $this->hasOne(\App\Models\Patient::class);
    }

    // Relation avec le medecin
    public function medecin()
    {
        return $this->hasOne(\App\Models\Medecin::class);
    }

}
