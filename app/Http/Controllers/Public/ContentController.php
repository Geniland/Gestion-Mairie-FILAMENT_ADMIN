<?php
namespace App\Http\Controllers\Public;
use App\Models\Service;
use App\Models\Actualite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function services()
    {
        try {
            $services = Service::all();
            
            if ($services->isEmpty()) {
                return response()->json([
                    [
                        'id' => 1,
                        'titre' => 'État Civil',
                        'description' => 'Demandes d’actes de naissance, mariage, décès.',
                        'icon' => 'fas fa-users'
                    ],
                    [
                        'id' => 2,
                        'titre' => 'Taxes & Impôts',
                        'description' => 'Paiement sécurisé de vos taxes communales.',
                        'icon' => 'fas fa-file-invoice-dollar'
                    ]
                ]);
            }

            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur services : ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualites()
    {
        try {
            $actualites = Actualite::whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->get();

            if ($actualites->isEmpty()) {
                return response()->json([
                    [
                        'id' => 1,
                        'titre' => 'Nouvelle plateforme en ligne',
                        'resume' => 'La mairie lance son portail de services numériques.',
                        'image' => 'https://picsum.photos/800/400?random=1',
                        'published_at' => now()->subDays(2)->toDateTimeString()
                    ]
                ]);
            }

            return response()->json($actualites);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur actualités : ' . $e->getMessage()
            ], 500);
        }
    }

    // CRUD Methods for Admin
    public function storeService(Request $request)
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);
            $service = Service::create($validated);
            return response()->json($service, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur création service : ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateService(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->update($request->all());
            return response()->json($service);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur modification service : ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyService($id)
    {
        try {
            Service::destroy($id);
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur suppression service : ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeActualite(Request $request)
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'resume' => 'nullable|string',
                'contenu' => 'nullable|string',
                'image' => 'nullable|string',
                'published_at' => 'nullable|date',
            ]);
            if (!isset($validated['published_at'])) $validated['published_at'] = now();
            $actualite = Actualite::create($validated);
            return response()->json($actualite, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur création actualité : ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateActualite(Request $request, $id)
    {
        try {
            $actualite = Actualite::findOrFail($id);
            $actualite->update($request->all());
            return response()->json($actualite);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur modification actualité : ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyActualite($id)
    {
        try {
            Actualite::destroy($id);
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur suppression actualité : ' . $e->getMessage()
            ], 500);
        }
    }
}
