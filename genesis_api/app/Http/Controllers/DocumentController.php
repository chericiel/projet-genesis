<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $user = $request->user();

        // Vérifie que l'utilisateur est un médecin ou un patient
        if (!in_array($user->role->libelle, ['patient', 'medecin'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Validation du fichier
        $validated = $request->validate([
            'document' => 'required|file|max:10240', // max 10 MB
            'type' => 'nullable|string' // type facultatif : radio, ordonnance, etc.
        ]);

        // Enregistrement du fichier dans storage/app/public/documents
        $path = $request->file('document')->store('documents', 'public');

        // Création du document dans la base
        $doc = Document::create([
            'user_id' => $user->id,
            'nom_fichier' => $request->file('document')->getClientOriginalName(),
            'chemin_fichier' => $path,
            'type' => $validated['type'] ?? null,
        ]);

        return response()->json([
            'message' => 'Document uploadé avec succès.',
            'document' => $doc
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $documents = $user->documents()->latest()->get();

        return response()->json([
            'documents' => $documents
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $document = Document::where('id', $id)->where('user_id', $user->id)->first();

        if (!$document) {
            return response()->json(['message' => 'Document non trouvé ou accès non autorisé.'], 404);
        }

        // Supprimer le fichier physique
        if (\Storage::disk('public')->exists($document->chemin_fichier)) {
            \Storage::disk('public')->delete($document->chemin_fichier);
        }

        // Supprimer l'entrée en base
        $document->delete();

        return response()->json(['message' => 'Document supprimé avec succès.']);
    }
}
