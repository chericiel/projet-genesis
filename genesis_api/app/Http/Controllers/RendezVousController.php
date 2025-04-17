<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RendezVous;
use App\Models\Patient;
use App\Models\Medecin;

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
            'statut' => 'planifié'
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

    // Annuler un rendez-vous
    public function destroy($id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->delete();

        return response()->json([
            'message' => 'Rendez-vous annulé avec succès.'
        ]);
    }
}
