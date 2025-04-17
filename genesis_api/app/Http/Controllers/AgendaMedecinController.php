<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgendaMedecin;


class AgendaMedecinController extends Controller
{
    // Ajouter un créneau
    public function store(Request $request)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est bien un médecin
        if (!$user->medecin) {
            return response()->json(['message' => 'Seuls les médecins peuvent gérer leur agenda.'], 403);
        }

        // ✅ VALIDATION
        $validated = $request->validate([
            'jour' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'bloque' => 'boolean'
        ]);

        $agenda = AgendaMedecin::create([
            'medecin_id' => $user->medecin->id,
            'jour' => $validated['jour'],
            'heure_debut' => $validated['heure_debut'],
            'heure_fin' => $validated['heure_fin'],
            'bloque' => $validated['bloque'] ?? false
        ]);

        return response()->json([
            'message' => 'Créneau ajouté avec succès.',
            'agenda' => $agenda
        ]);
    }

    // Voir les créneaux du médecin connecté
    public function index(Request $request)
    {
        $user = $request->user()->load('medecin');

        if (!$user->medecin) {
            return response()->json(['message' => 'Seuls les médecins peuvent consulter leur agenda.'], 403);
        }

        $agenda = AgendaMedecin::where('medecin_id', $user->medecin->id)
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();

        return response()->json([
            'agenda' => $agenda
        ]);
    }


    // Supprimer un créneau
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->medecin) {
            return response()->json(['message' => 'Seuls les médecins peuvent supprimer un créneau.'], 403);
        }

        $agenda = AgendaMedecin::where('id', $id)
            ->where('medecin_id', $user->medecin->id)
            ->first();

        if (!$agenda) {
            return response()->json(['message' => 'Créneau introuvable ou non autorisé.'], 404);
        }

        $agenda->delete();

        return response()->json(['message' => 'Créneau supprimé avec succès.']);
    }
}
