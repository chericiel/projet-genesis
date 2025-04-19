<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\AgendaMedecinController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\PaiementController;



// Route de test protégée (auth:sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'message' => 'Bienvenue sur ton espace sécurisé !'
    ]);
});

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Routes protégées du module rendez-vous
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rendez-vous', [RendezVousController::class, 'index']);
    Route::post('/rendez-vous', [RendezVousController::class, 'store']);
    Route::put('/rendez-vous/{id}', [RendezVousController::class, 'update']);
    Route::delete('/rendez-vous/{id}', [RendezVousController::class, 'destroy']);
});
// Modifier le mot de passe
Route::post('/request-reset-code', [AuthController::class, 'requestResetCode']);

// Réinitialiser le mot de passe
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Routes protégées du module agenda médecin
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/agenda', [AgendaMedecinController::class, 'store']);        // Ajouter un créneau
    Route::get('/agenda', [AgendaMedecinController::class, 'index']);         // Lister mes créneaux
    Route::delete('/agenda/{id}', [AgendaMedecinController::class, 'destroy']); // Supprimer un créneau
});

// Routes protégées du module consultations
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/consultations', [ConsultationController::class, 'store']);
});

// Routes protégées du module notifications 
Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);
Route::middleware('auth:sanctum')->post('/notifications', [NotificationController::class, 'store']);
Route::patch('/notifications/{id}/lu', [NotificationController::class, 'markAsRead']);

// Routes protégées du module documents
Route::middleware('auth:sanctum')->post('/documents', [DocumentController::class, 'upload']);
Route::middleware('auth:sanctum')->get('/documents', [DocumentController::class, 'index']);
Route::middleware('auth:sanctum')->delete('/documents/{id}', [DocumentController::class, 'destroy']);

// Routes protégées du module historique médical
Route::middleware('auth:sanctum')->get('/patient/historique', [HistoriqueController::class, 'index']);
Route::middleware('auth:sanctum')->get('/patient/historique/{id}', [HistoriqueController::class, 'show']);

// Routes protégées du module paiements
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/paiements', [PaiementController::class, 'store']);
    Route::get('/paiements', [PaiementController::class, 'index']);
    Route::get('/paiements/{id}', [PaiementController::class, 'show']);
});
