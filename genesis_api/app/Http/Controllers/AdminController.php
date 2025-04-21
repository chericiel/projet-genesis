<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Connection;
use App\Models\RendezVous;
use App\Models\Paiement;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // =====================================================
    //  GESTION DES UTILISATEURS
    // =====================================================

    //  Créer un utilisateur (admin, médecin, patient)
    public function createUser(Request $request)
    {
        //   Validation
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|max:10',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'required|email|unique:connections,email',
            'mot_de_passe' => 'required|string|min:6',
            'role' => 'required|in:patient,medecin,administrateur', // libellé
        ]);

        //  Convertir libellé role en ID
        $roleId = Role::where('libelle', $validated['role'])->value('id');

        if (!$roleId) {
            return response()->json(['message' => 'Rôle invalide.'], 422);
        }

        //  Créer l'utilisateur
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'sexe' => $validated['sexe'],
            'date_naissance' => $validated['date_naissance'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'role_id' => $roleId,
        ]);

        //  Connexion
        Connection::create([
            'user_id' => $user->id,
            'email' => $validated['email'],
            'mot_de_passe' => bcrypt($validated['mot_de_passe']),
        ]);

        //  Ajout dans table spécifique si besoin
        if ($validated['role'] === 'administrateur') {
            Administrateur::create(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'patient') {
            \App\Models\Patient::create(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'medecin') {
            \App\Models\Medecin::create(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => $user
        ]);
    }

    // ✅ Lister tous les utilisateurs avec leur rôle
    public function indexUsers()
    {
        return response()->json(User::with('role')->get());
    }

    // ✅ Voir un utilisateur spécifique
    public function showUser($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json($user);
    }

    

    // ✅ Modifier un utilisateur (changer rôle, adresse, etc.)
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $connection = $user->connection;

        $validated = $request->validate([
            'nom' => 'sometimes|string',
            'prenom' => 'sometimes|string',
            'adresse' => 'sometimes|string',
            'telephone' => 'sometimes|string',
            'role_id' => 'sometimes|exists:roles,id',
            'email' => 'sometimes|email|unique:connections,email,' . $connection->id,
            'mot_de_passe' => 'sometimes|string|min:6'
        ]);

        $user->update($request->only(['nom', 'prenom', 'adresse', 'telephone', 'role_id']));

        if ($connection && isset($validated['email'])) {
            $connection->email = $validated['email'];
        }

        if ($connection && isset($validated['mot_de_passe'])) {
            $connection->mot_de_passe = bcrypt($validated['mot_de_passe']);
        }

        if ($connection) $connection->save();

        return response()->json(['message' => 'Utilisateur mis à jour.', 'user' => $user]);
    }


    // ✅ Supprimer un utilisateur
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    // =====================================================
    // 📅 GESTION DES RENDEZ-VOUS
    // =====================================================

    // ✅ Voir tous les rendez-vous
    public function indexRendezVous()
    {
        return response()->json(
            RendezVous::with(['patient.user', 'medecin.user'])->get()
        );
    }

    // =====================================================
    // 💳 GESTION DES PAIEMENTS
    // =====================================================

    // ✅ Voir tous les paiements
    public function indexPaiements()
    {
        return response()->json(
            Paiement::with('consultation.rendezVous.patient.user')->get()
        );
    }

    // ✅ Voir un paiement spécifique
    public function showPaiement($id)
    {
        $paiement = Paiement::with('consultation.rendezVous.patient.user')->find($id);

        if (!$paiement) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        return response()->json($paiement);
    }

    // ✅ Mettre à jour le statut d’un paiement
    public function updatePaiement(Request $request, $id)
    {
        $paiement = Paiement::findOrFail($id);

        $request->validate([
            'statut' => 'required|string'
        ]);

        $paiement->statut = $request->statut;
        $paiement->save();

        return response()->json([
            'message' => 'Paiement mis à jour.',
            'paiement' => $paiement
        ]);
    }
}
