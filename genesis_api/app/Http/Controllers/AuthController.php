<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\EmailVerification;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
    // Endpoint pour l'inscription
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|max:10',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'required|email|unique:connections,email',
            'mot_de_passe' => 'required|string|min:6'
        ]);

        // Permet d'attribuer le role patient directement à l'utilisateur lorsqu'il s'inscrit
        $roleId = \App\Models\Role::where('libelle', 'patient')->value('id');

        // Création de l'utilisateur
        $user = User::create([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'sexe' => $validatedData['sexe'],
            'date_naissance' => $validatedData['date_naissance'] ?? null,
            'adresse' => $validatedData['adresse'] ?? null,
            'telephone' => $validatedData['telephone'] ?? null,
            'role_id' => $roleId,
        ]);

        // Création de la connexion
        Connection::create([
            'user_id' => $user->id,
            'email' => $validatedData['email'],
            'mot_de_passe' => bcrypt($validatedData['mot_de_passe']),
        ]);

        // Création d'un token d'accès via Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur inscrit avec succès !',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Endpoint pour la connexion
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required'
        ]);

        // Récupérer la connexion via l'email
        $connection = Connection::where('email', $credentials['email'])->first();

        // Vérifier si la connexion existe et si le mot de passe est correct
        if (!$connection || !Hash::check($credentials['mot_de_passe'], $connection->mot_de_passe)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Récupérer l'utilisateur associé à la connexion
        $user = $connection->user;

        // Génération du code de vérification
        $code = rand(100000, 999999);

        EmailVerification::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Envoi du mail (à configurer dans .env !)
        Mail::to($credentials['email'])->send(new VerificationCodeMail($code));



        return response()->json([
            'message' => 'Un code de vérification a été envoyé à votre adresse email.'
        ]);
    }

    public function verifyCode(Request $request)
    {   
        // Valider les données reçues : email et code obligatoire
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);
        // Vérifier si l'utilisateur existe
        $connection = Connection::where('email', $request->email)->first();

        // Vérifier si le code de vérification est valide ou retourne une erreur
        if (!$connection){
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Récupérer l'utilisateur lié à cette connexion
        $user = $connection->user;

        // Rechercher le code de vérification correspondant à l'utilisateur,
        // qui n'est pas expiré (expires_at > maintenant), et prendre le plus récent
        $codeEntry = EmailVerification::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->latest()->first();

        if (!$codeEntry) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 403);
        }

        // Génération du token après validation
        $token = $user->createToken('api-token')->plainTextToken;

        // Optionnel : supprimer le code une fois utilisé
        $codeEntry->delete();

        return response()->json([
            'message' => 'Connexion validée.',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }
}

