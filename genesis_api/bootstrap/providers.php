<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,      // ← notre provider d’autorisations
    App\Providers\RouteServiceProvider::class, // ← notre provider de routes
    App\Providers\EventServiceProvider::class, // ← notre provider d’événements
];
