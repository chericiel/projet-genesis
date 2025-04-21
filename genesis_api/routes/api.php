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
use App\Http\Controllers\AdminController;

// 🧪 Route de test sécurisée
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'message' => 'Bienvenue sur ton espace sécurisé !'
    ]);
});

/**
 * =============================
 * 🔐 AUTHENTIFICATION PUBLIQUE
 * =============================
 */

// 👉 Inscription selon le rôle
Route::post('/register/patient', [AuthController::class, 'registerPatient']);
Route::post('/register/medecin', [AuthController::class, 'registerMedecin']);
Route::post('/register/admin', [AuthController::class, 'registerAdmin']);

// 👉 Connexion & vérification
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/request-reset-code', [AuthController::class, 'requestResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/**
 * =============================
 * 📅 MODULE RENDEZ-VOUS (PATIENT)
 * =============================
 */
Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::get('/rendez-vous', [RendezVousController::class, 'index']);
    Route::post('/rendez-vous', [RendezVousController::class, 'store']);
    Route::put('/rendez-vous/{id}', [RendezVousController::class, 'update']);
    Route::delete('/rendez-vous/{id}', [RendezVousController::class, 'destroy']);
});

/**
 * =============================
 * 🩺 AGENDA & CONSULTATIONS, RENDEZ-VOUS(MEDECIN)
 * =============================
 */
Route::middleware(['auth:sanctum', 'role:medecin'])->group(function () {
    // Agenda
    Route::post('/agenda', [AgendaMedecinController::class, 'store']);
    Route::get('/agenda', [AgendaMedecinController::class, 'index']);
    Route::delete('/agenda/{id}', [AgendaMedecinController::class, 'destroy']);

    // Rendez-vous
    Route::patch('/rendez-vous/{id}/annuler', [RendezVousController::class, 'annulerParMedecin']);
    Route::patch('/rendez-vous/{id}/valider', [RendezVousController::class, 'valider']);
    Route::get('/rendez-vous/en-attente', [RendezVousController::class, 'enAttente']);

    // Consultation
    Route::post('/consultations', [ConsultationController::class, 'store']);

    // Historique
    Route::middleware(['auth:sanctum', 'role:medecin'])->get('/medecin/historique', [HistoriqueController::class, 'medecinIndex']);
});


/**
 * =============================
 * 🔔 MODULE NOTIFICATIONS
 * (Patient & Médecin)
 * =============================
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/{id}/lu', [NotificationController::class, 'markAsRead']);
    Route::patch('/rendez-vous/{id}/valider', [RendezVousController::class, 'valider']);

});

/**
 * =============================
 * 📎 MODULE DOCUMENTS
 * (Patient & Médecin)
 * =============================
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/documents', [DocumentController::class, 'upload']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
});


/**
 * =============================
 * 🧠 MODULE HISTORIQUE MÉDICAL (PATIENT)
 * =============================
 */
Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::get('/patient/historique', [HistoriqueController::class, 'index']);
    Route::get('/patient/historique/{id}', [HistoriqueController::class, 'show']);
    Route::post('/paiements', [PaiementController::class, 'store']);
});

/**
 * =============================
 * 💳 MODULE PAIEMENTS (Lecture commune)
 * =============================
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/paiements', [PaiementController::class, 'index']);
    Route::get('/paiements/{id}', [PaiementController::class, 'show']);
});

/**
 * =============================
 * 🛠️ MODULE ADMINISTRATION (GATE admin)
 * =============================
 */
Route::middleware(['auth:sanctum', 'can:admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class,'summary']);
    Route::get('/users', [DashboardController::class,'usersStats']);
    Route::get('/rendez-vous', [DashboardController::class,'rdvStats']);
    Route::get('/paiements', [DashboardController::class,'paiementsStats']);

    // 👥 Utilisateurs
    Route::get('/users', [AdminController::class, 'indexUsers']);
    Route::get('/users/{id}', [AdminController::class, 'showUser']);
    Route::patch('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
    Route::post('/users', [AdminController::class, 'createUser']);

    // 📅 Rendez-vous
    Route::get('/rendez-vous', [AdminController::class, 'indexRendezVous']);

    // 💳 Paiements
    Route::get('/paiements', [AdminController::class, 'indexPaiements']);
    Route::get('/paiements/{id}', [AdminController::class, 'showPaiement']);
    Route::patch('/paiements/{id}', [AdminController::class, 'updatePaiement']);
});
