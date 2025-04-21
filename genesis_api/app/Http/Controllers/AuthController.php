<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Connection;
use App\Models\Administrateur;
use App\Models\ResetCode;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerificationCodeMail;
use App\Mail\ResetPasswordCodeMail;

class AuthController extends Controller
{
    // ✅ 1. Enregistrement de l'utilisateur selon la route utilisée
    public function registerPatient(Request $request)   
    {
        return $this->registerUser($request, 'patient');
    }

    public function registerMedecin(Request $request)
    {
        return $this->registerUser($request, 'medecin');
    }

    public function registerAdmin(Request $request)
    {
        return $this->registerUser($request, 'administrateur');
    }

    private function registerUser(Request $request, $roleLibelle)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|max:10',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'required|email|unique:connections,email',
            'mot_de_passe' => 'required|string|min:6'
        ]);

        $roleId = Role::where('libelle', $roleLibelle)->value('id');

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'sexe' => $validated['sexe'],
            'date_naissance' => $validated['date_naissance'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'role_id' => $roleId,
        ]);

        Connection::create([
            'user_id' => $user->id,
            'email' => $validated['email'],
            'mot_de_passe' => bcrypt($validated['mot_de_passe']),
        ]);

        // 👤 Enregistrement spécifique selon le rôle
        if ($roleLibelle === 'patient') {
            \App\Models\Patient::create(['user_id' => $user->id]);
        } elseif ($roleLibelle === 'medecin') {
            \App\Models\Medecin::create([
                'user_id' => $user->id,
                'specialite' => 'Généraliste', // Valeur par défaut modifiable plus tard
                'experience' => '0 an',
                'honoraires' => 0
            ]);
        } elseif ($roleLibelle === 'administrateur') {
            \App\Models\Administrateur::create(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'Inscription réussie en tant que ' . $roleLibelle . '.',
            'user' => $user
        ]);



        // 🔍 Récupération du rôle selon la route appelée
        $roleRoute = $request->route()->getName(); // ex: register.patient
        $roleMap = [
            'register.patient' => 'patient',
            'register.medecin' => 'medecin',
            'register.admin'   => 'administrateur',
        ];

        $libelleRole = $roleMap[$roleRoute] ?? 'patient';
        $roleId = Role::where('libelle', $libelleRole)->value('id');

        // 👤 Création de l'utilisateur
        $user = User::create([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'sexe' => $validatedData['sexe'],
            'date_naissance' => $validatedData['date_naissance'] ?? null,
            'adresse' => $validatedData['adresse'] ?? null,
            'telephone' => $validatedData['telephone'] ?? null,
            'role_id' => $roleId,
        ]);

        // 🔐 Création des identifiants de connexion
        Connection::create([
            'user_id' => $user->id,
            'email' => $validatedData['email'],
            'mot_de_passe' => bcrypt($validatedData['mot_de_passe']),
        ]);

        // ➕ Ajouter dans la table administrateurs si besoin
        if ($libelleRole === 'administrateur') {
            Administrateur::create(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => "Inscription $libelleRole réussie !",
            'user' => $user,
            'role' => $libelleRole
        ]);
    }

    // ✅ 2. Connexion (génère un code à valider par email)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required'
        ]);

        $connection = Connection::where('email', $credentials['email'])->first();

        if (!$connection || !Hash::check($credentials['mot_de_passe'], $connection->mot_de_passe)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $user = $connection->user;

        $code = rand(100000, 999999);

        EmailVerification::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($credentials['email'])->send(new VerificationCodeMail($code));

        return response()->json([
            'message' => 'Un code de vérification a été envoyé à votre adresse email.'
        ]);
    }

    // ✅ 3. Vérification du code et génération du token
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $connection = Connection::where('email', $request->email)->first();

        if (!$connection) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $user = $connection->user;

        $codeEntry = EmailVerification::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->latest()->first();

        if (!$codeEntry) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // ➕ Ajouter dans la table administrateurs si nécessaire
        $adminRoleId = Role::where('libelle', 'administrateur')->value('id');

        if ($user->role_id === $adminRoleId && !Administrateur::where('user_id', $user->id)->exists()) {
            Administrateur::create(['user_id' => $user->id]);
        }

        $codeEntry->delete();

        return response()->json([
            'message' => 'Connexion validée.',
            'token' => $token,
            'user' => $user,
            'role' => $user->role->libelle ?? null
        ]);
    }

    // ✅ 4. Déconnexion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // 🔁 5. Demande de code pour réinitialisation du mot de passe
    public function requestResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $connection = Connection::where('email', $request->email)->first();

        if (!$connection) {
            return response()->json(['message' => 'Aucun utilisateur trouvé avec cet email.'], 404);
        }

        $user = $connection->user;
        $code = rand(100000, 999999);

        ResetCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10)
        ]);

        Mail::to($request->email)->send(new ResetPasswordCodeMail($code));

        return response()->json([
            'message' => 'Un code de réinitialisation a été envoyé à votre adresse email.'
        ]);
    }

    // 🔄 6. Réinitialisation du mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'mot_de_passe' => 'required|string|min:6|confirmed'
        ]);

        $connection = Connection::where('email', $request->email)->first();

        if (!$connection) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $user = $connection->user;

        $resetCode = ResetCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$resetCode) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 403);
        }

        $connection->mot_de_passe = bcrypt($request->mot_de_passe);
        $connection->save();

        $resetCode->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
