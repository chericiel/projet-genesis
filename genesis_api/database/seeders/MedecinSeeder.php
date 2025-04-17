<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Connection;
use App\Models\Role;
use App\Models\Medecin;

class MedecinSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère le rôle "medecin"
        $roleMedecin = Role::where('libelle', 'medecin')->first();

        // Crée un utilisateur pour le médecin
        $user = User::create([
            'nom' => 'Marie',
            'prenom' => 'MedecinTest',
            'sexe' => 'F',
            'date_naissance' => '1985-02-20',
            'adresse' => 'Yaoundé',
            'telephone' => '670000000',
            'role_id' => $roleMedecin->id,
        ]);

        // Crée la connexion associée à cet utilisateur
        Connection::create([
            'user_id' => $user->id,
            'email' => 'aronwalace95@gmal.com',
            'mot_de_passe' => bcrypt('azerty123'),
        ]);

        // Crée l'entrée spécifique au médecin
        Medecin::create([
            'user_id' => $user->id,
            'specialite' => 'Généraliste',
            'experience' => '10 ans',
            'honoraires' => 15000,
        ]);
    }
}
