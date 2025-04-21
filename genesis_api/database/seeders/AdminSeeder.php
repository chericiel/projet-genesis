<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Connection;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère l'ID du rôle administrateur
        $roleAdminId = Role::where('libelle', 'administrateur')->value('id');

        // Crée (ou retrouve) l'utilisateur Admin
        $user = User::firstOrCreate(
            ['nom' => 'Admin', 'prenom' => 'Genesis'],
            [
                'sexe' => 'M',
                'date_naissance' => now()->subYears(30),
                'adresse' => 'Siège',
                'telephone' => '0000000000',
                'role_id' => $roleAdminId,
            ]
        );

        // Crée (ou retrouve) sa connexion (email + mot de passe)
        Connection::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email' => 'admin@genesis.local',
                'mot_de_passe' => bcrypt('admin123')
            ]
        );
    }
}
