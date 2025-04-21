<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;

class HistoriqueController extends Controller
{
    // Liste de toutes les consultations d'un patient
    public function index(Request $request)
    {
        $user = $request->user()->load('role', 'patient');

        if ($user->role->libelle !== 'patient') {
            return response()->json(['message' => 'Seuls les patients peuvent consulter leur historique.'], 403);
        }

        $consultations = \App\Models\Consultation::with(['rendezVous.medecin.user', 'documents'])
            ->whereHas('rendezVous', function ($query) use ($user) {
                $query->where('patient_id', $user->patient->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Historique médical récupéré avec succès.',
            'historique' => $consultations
        ]);
    }

    public function show($id, Request $request)
    {
        $user = $request->user()->load('role');

        if ($user->role->libelle !== 'patient') {
            return response()->json(['message' => 'Accès réservé aux patients.'], 403);
        }

        // Vérifie que la consultation appartient au patient connecté
        $consultation = \App\Models\Consultation::with(['rendezVous.medecin.user', 'documents'])
        ->whereHas('rendezVous', function ($query) use ($user) {
            $query->where('patient_id', $user->patient->id);
        })
        ->find($id); // find doit venir directement après la query

        if (!$consultation) {
            return response()->json(['message' => 'Consultation introuvable ou non autorisée.'], 404);
        }

        return response()->json([
            'message' => 'Détail de la consultation.',
            'consultation' => $consultation
        ]);
    }

    // Liste les consultations effectué par le medecin
    public function medecinIndex(Request $request)
    {
        $user = $request->user()->load('medecin');

        $consultations = \App\Models\Consultation::whereHas('rendezVous', function ($q) use ($user) {
            $q->where('medecin_id', $user->medecin->id);
        })->with('rendezVous.patient.user')->latest()->get();

        return response()->json([
            'message' => 'Historique des consultations effectuées',
            'consultations' => $consultations
        ]);
    }
}
