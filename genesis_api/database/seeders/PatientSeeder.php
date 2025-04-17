<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Connection;
use App\Models\Role;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère le rôle "patient"
        $rolePatient = Role::where('libelle', 'patient')->first();

        // Crée un utilisateur avec les informations de base
        $user = User::create([
            'nom' => 'Jean',
            'prenom' => 'PatientTest',
            'sexe' => 'M',
            'date_naissance' => '1990-01-01',
            'adresse' => 'Douala',
            'telephone' => '690000000',
            'role_id' => $rolePatient->id,
        ]);

        // Crée la connexion associée à cet utilisateur
        Connection::create([
            'user_id' => $user->id,
            'email' => 'patient@test.com',
            'mot_de_passe' => bcrypt('azerty123'),
        ]);

        // Crée l'entrée spécifique au patient
        Patient::create([
            'user_id' => $user->id,
            'numero_securite_sociale' => 'PT12345678',
        ]);
    }
}
