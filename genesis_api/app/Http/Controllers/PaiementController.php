<?php
namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Consultation;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // Validation des données envoyées dans la requete
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'montant' => 'required|numeric',
            'mode' => 'required|string',
            'statut' => 'required|string',
            'date_paiement' => 'required|date',
        ]);

        // Récupération de la consultation associée à l'ID
        $consultation = Consultation::find($validated['consultation_id']);

        // Vérification que le paiement est effectué par le patient associé à la consultation
        if ($consultation->rendezVous->patient_id !== $user->id) {
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
        // Vérification que l'utilisateur est authentifié
        $user = $request->user();

        // Récupération des paiements associés aux consultations du patient
        $paiements = Paiement::whereHas('consultation.rendezVous', function ($q) use ($user) {
            $q->where('patient_id', $user->id);
        })->get();

        // Retourne tous les paiements
        return response()->json($paiements);
    }
}



