<?php
namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Consultation;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user()->load('patient');

        // Validation des données envoyées dans la requete
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'montant' => 'required|numeric',
            'mode' => 'required|string',
            'statut' => 'required|string',
            'date_paiement' => 'required|date',
        ]);

        // Récupération de la consultation associée à l'ID
        $consultation = Consultation::with('rendezVous')->find($validated['consultation_id']);

        // Vérification que le paiement est effectué par le patient associé à la consultation
        if (!$user->patient || $consultation->rendezVous->patient_id !== $user->patient->id) {
            return response()->json(['message' => 'Paiement non autorisé.'], 403);
        }

        // Création du paiement
        $paiement = Paiement::create([
            'consultation_id' => $validated['consultation_id'],
            'montant' => $validated['montant'],
            'mode' => $validated['mode'],
            'statut' => $validated['statut'],
            'date_paiement' => $validated['date_paiement'],
        ]);

        // Retourner une réponse JSON avec le paiement créé
        return response()->json([
            'message' => 'Paiement enregistré avec succès.',
            'paiement' => $paiement
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user()->load(['role', 'patient', 'medecin']);
        $role = $user->role->libelle;

        //  Si ADMIN → voir tous les paiements
        if ($role === 'administrateur') {
            $paiements = \App\Models\Paiement::with('consultation.rendezVous.patient.user')->get();

            return response()->json([
                'message' => 'Liste complète des paiements (admin)',
                'paiements' => $paiements
            ]);
        }

        //  Si PATIENT → voir ses paiements
        if ($role === 'patient' && $user->patient) {
            $paiements = \App\Models\Paiement::whereHas('consultation.rendezVous', function ($query) use ($user) {
                $query->where('patient_id', $user->patient->id);
            })->with('consultation.rendezVous.medecin.user')->get();

            return response()->json([
                'message' => 'Liste des paiements du patient',
                'paiements' => $paiements
            ]);
        }

        //  Si MEDECIN → voir les paiements liés à ses consultations
        if ($role === 'medecin' && $user->medecin) {
            $paiements = \App\Models\Paiement::whereHas('consultation.rendezVous', function ($query) use ($user) {
                $query->where('medecin_id', $user->medecin->id);
            })->with('consultation.rendezVous.patient.user')->get();

            return response()->json([
                'message' => 'Liste des paiements des consultations du médecin',
                'paiements' => $paiements
            ]);
        }

        // Accès non autorisé
        return response()->json(['message' => 'Rôle non autorisé pour afficher les paiements.'], 403);
    }
}



