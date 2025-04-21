<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RendezVous;
use App\Models\Patient;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\User; 

class RendezVousController extends Controller
{

    // Voir les rendez-vous du user connecté
    public function index(Request $request)
    {
        $user = $request->user()->load(['patient', 'medecin', 'role']);
        $role = $user->role->libelle;


        // Récupérer le statut à filtrer (facultatif)
        $statut = $request->query('statut');

        // Initialiser $query à null
        $query = null;

        // Vérification robuste des rôles et des relations
        if ($role === 'patient' && $user->patient !== null) {
            $query = RendezVous::where('patient_id', $user->patient->id)->with('medecin.user');
        }

        if ($role === 'medecin' && $user->medecin !== null) {
            $query = RendezVous::where('medecin_id', $user->medecin->id)->with('patient.user');
        }

        // Si $query est toujours null => accès interdit
        if (!$query) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($statut) {
            $query->where('statut', $statut); // appliquer le filtre si présent
        }
    
        $rdvs = $query->orderBy('date_rdv')->get();
    
        return response()->json([
            'message' => 'Liste des rendez-vous' . ($statut ? " ($statut)" : '') . '.',
            'rendez_vous' => $rdvs
        ]);
    }

    

    //  Créer un nouveau rendez-vous
    public function store(Request $request)
    {
        $user = $request->user()->load(['patient', 'role']);

        if ($user->role->libelle !== 'patient') {
            return response()->json(['message' => 'Seuls les patients peuvent prendre un rendez-vous.'], 403);
        }

        $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'date_rdv' => 'required|date',
            'heure_rdv' => 'required'
        ]);

        $rdv = RendezVous::create([
            'patient_id' => $user->patient->id,
            'medecin_id' => $request->medecin_id,
            'date_rdv' => $request->date_rdv,
            'heure_rdv' => $request->heure_rdv,
            'statut' => 'en attente'
        ]);

        // Notification au médecin
        $medecin = \App\Models\Medecin::with('user')->find($request->medecin_id);
        $medecinUser = $medecin?->user;

        Notification::create([
            'user_id' => $medecinUser->id,
            'titre' => 'Nouveau rendez-vous reçu',
            'message' => 'Vous avez un nouveau rendez-vous planifié avec le patient ' . $user->prenom . ' ' . $user->nom . '.',
        ]);

        return response()->json([
            'message' => 'Rendez-vous planifié avec succès.',
            'rdv' => $rdv
        ]);
    }

    //  Modifier un rendez-vous
    public function update(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);

        $rdv->update($request->only('date_rdv', 'heure_rdv', 'statut'));

        return response()->json([
            'message' => 'Rendez-vous mis à jour.',
            'rdv' => $rdv
        ]);
    }

    // 🔍 Lister les rendez-vous "en attente" du médecin connecté
    public function enAttente(Request $request)
    {
        $user = $request->user()->load('medecin', 'role');

        if (!$user->medecin || $user->role->libelle !== 'medecin') {
            return response()->json(['message' => 'Seuls les médecins peuvent voir leurs rendez-vous.'], 403);
        }

        $rdvs = RendezVous::with('patient.user')
            ->where('medecin_id', $user->medecin->id)
            ->where('statut', 'en attente')
            ->orderBy('date_rdv')
            ->get();

        return response()->json([
            'message' => 'Rendez-vous en attente.',
            'rendez_vous' => $rdvs
        ]);
    }


    // Valider un rendez-vous par le médecin
    public function valider(Request $request, $id)
    {
        $user = $request->user()->load('medecin', 'role');

        if ($user->role->libelle !== 'medecin') {
            return response()->json(['message' => 'Seuls les médecins peuvent valider un rendez-vous.'], 403);
        }

        $rdv = RendezVous::with('patient.user')->where('id', $id)
            ->where('medecin_id', $user->medecin->id)
            ->first();

        if (!$rdv) {
            return response()->json(['message' => 'Rendez-vous introuvable ou non autorisé.'], 404);
        }

        $rdv->statut = 'planifié';
        $rdv->save();

        // 🔔 Notifier le patient
        Notification::create([
            'user_id' => $rdv->patient->user->id,
            'titre' => 'Rendez-vous confirmé',
            'message' => 'Votre rendez-vous avec le médecin le ' . $rdv->date_rdv . ' à ' . $rdv->heure_rdv . ' a été confirmé.'
        ]);

        return response()->json([
            'message' => 'Rendez-vous validé avec succès.',
            'rdv' => $rdv
        ]);
    }


    // Annuler un rendez-vous
    public function destroy(Request $request, $id)
    {
        $user = $request->user()->load(['patient', 'role']);

        if (!$user->role || $user->role->libelle !== 'patient') {
            return response()->json(['message' => 'Seuls les patients peuvent annuler un rendez-vous.'], 403);
        }

        $rdv = RendezVous::find($id);

        if (!$rdv || $rdv->patient_id !== optional($user->patient)->id) {
            return response()->json(['message' => 'Rendez-vous introuvable ou non autorisé.'], 404);
        }

        // Au lieu de le supprimer, on change le statut
        $rdv->statut = 'annulé';
        $rdv->save();

        return response()->json([
            'message' => 'Rendez-vous annulé avec succès.',
            'rdv' => $rdv
        ]);
    }

    // Annuler un rendez-vous (médecin)
    public function annulerParMedecin(Request $request, $id)
    {
        $user = $request->user()->load(['medecin', 'role']);

        if (!$user->medecin || $user->role->libelle !== 'medecin') {
            return response()->json(['message' => 'Seuls les médecins peuvent annuler un rendez-vous.'], 403);
        }

        $rdv = RendezVous::where('id', $id)
            ->where('medecin_id', $user->medecin->id)
            ->first();

        if (!$rdv) {
            return response()->json(['message' => 'Rendez-vous introuvable ou non autorisé.'], 404);
        }

        $rdv->statut = 'annulé';
        $rdv->save();

        // 🔔 Notifier le patient que le médecin a annulé
        $rdv->load('patient.user');
        $patientUser = $rdv->patient->user;

        Notification::create([
            'user_id' => $patientUser->id,
            'titre' => 'Rendez-vous annulé',
            'message' => 'Votre rendez-vous prévu le ' . $rdv->date_rdv . ' à ' . $rdv->heure_rdv . ' a été annulé par le médecin.'
        ]);


        return response()->json([
            'message' => 'Rendez-vous annulé par le médecin.',
            'rendez_vous' => $rdv
        ]);
    }
}
