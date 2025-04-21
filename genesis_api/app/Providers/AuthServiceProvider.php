<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User; // ✅ C'est ce use qu’il faut !


class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // Permet à l'admin d'accéder à toutes les actions
        Gate::before(function (User $user, $ability) {
            return $user->role && $user->role->libelle === 'administrateur' ? true : null;
        });

        // Cette Gate s’appelle "admin" et ne passe que si role = administrateur
        Gate::define('admin', fn($user) => $user->role->libelle === 'administrateur');
        Gate::define('medecin', fn($user) => $user->role->libelle === 'medecin');
        Gate::define('patient', fn($user) => $user->role->libelle === 'patient');
        
    }
}
