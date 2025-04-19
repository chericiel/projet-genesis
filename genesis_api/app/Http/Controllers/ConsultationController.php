<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{   
    public function store(Request $request)
    {
        // Charger l'utilisateur connecté et ses relations
        $user = $request->user()->load('medecin', 'role');
        // Vérifier que l'utilisateur est un médecin
        if (!$user->medecin) {
            return response()->json(['message' => 'Seuls les médecins peuvent créer une consultation.'], 403);
        }

        // Validation des données
        $request->validate([
            'rendez_vous_id' => 'required|exists:rendez_vous,id',
            'diagnostic' => 'required|string',
            'note' => 'nullable|string'
        ]);

        // Vérifier si le RDV appartient bien à ce médecin
        $rdv = RendezVous::where('id', $request->rendez_vous_id)
            ->where('medecin_id', $user->medecin->id)
            ->first();

        if (!$rdv) {
            return response()->json(['message' => 'Rendez-vous non trouvé ou non autorisé.'], 404);
        }

        // Creer la consultation 
        $consultation = Consultation::create([
            'rendez_vous_id' => $rdv->id,
            'diagnostic' => $request->diagnostic,
            'note' => $request->note,
        ]);
        
        // Lier le document à la consultation
        if ($request->hasFile('document')) {
            $document = Document::create([
                'user_id' => $user->id,
                'nom_fichier' => $request->file('document')->getClientOriginalName(),
                'chemin_fichier' => $request->file('document')->store('documents', 'public'),
                'type' => 'consultation',
                'consultation_id' => $consultation->id,
            ]);

            // Associer le document à la consultation
            $consultation->documents()->save($document);
        }
        
        // Mettre à jour le statut du RDV
        $rdv->statut = 'terminé';
        $rdv->save();

        return response()->json([
            'message' => 'Consultation enregistrée avec succès.',
            'consultation' => $consultation
        ]);
    }
}

