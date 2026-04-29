<?php
namespace App\Http\Controllers\Public;
use App\Models\Service;
use App\Models\Actualite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function services()
    {
        try {
            $services = Service::all()->map(function($service) {
                if ($service->image) {
                    $service->image_url = asset('storage/' . $service->image);
                }
                return $service;
            });
            
            if ($services->isEmpty()) {
                return response()->json([
                    [
                        'id' => 1,
                        'titre' => 'État Civil',
                        'description' => 'Demandes d’actes de naissance, mariage, décès.',
                        'icon' => 'fas fa-users',
                        'image_url' => null
                    ],
                    [
                        'id' => 2,
                        'titre' => 'Taxes & Impôts',
                        'description' => 'Paiement sécurisé de vos taxes communales.',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'image_url' => null
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
                ->get()
                ->map(function($item) {
                    if ($item->image && !filter_var($item->image, FILTER_VALIDATE_URL)) {
                        $item->image = asset('storage/' . $item->image);
                    }
                    return $item;
                });

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

    public function actualite($id)
    {
        try {
            $actualite = Actualite::findOrFail($id);
            if ($actualite->image && !filter_var($actualite->image, FILTER_VALIDATE_URL)) {
                $actualite->image = asset('storage/' . $actualite->image);
            }
            return response()->json([
                'status' => true,
                'data' => $actualite
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Actualité introuvable'
            ], 404);
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
                'image' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('services', 'public');
                $validated['image'] = $path;
            }

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
            $validated = $request->validate([
                'titre' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($service->image) {
                    Storage::disk('public')->delete($service->image);
                }
                $path = $request->file('image')->store('services', 'public');
                $validated['image'] = $path;
            }

            $service->update($validated);
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
            $service = Service::findOrFail($id);
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $service->delete();
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
                'image' => 'nullable|image|max:4096',
                'published_at' => 'nullable|date',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('actualites', 'public');
                $validated['image'] = $path;
            }

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
            $validated = $request->validate([
                'titre' => 'sometimes|required|string|max:255',
                'resume' => 'nullable|string',
                'contenu' => 'nullable|string',
                'image' => 'nullable|image|max:4096',
                'published_at' => 'nullable|date',
            ]);

            if ($request->hasFile('image')) {
                if ($actualite->image) {
                    Storage::disk('public')->delete($actualite->image);
                }
                $path = $request->file('image')->store('actualites', 'public');
                $validated['image'] = $path;
            }

            $actualite->update($validated);
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
            $actualite = Actualite::findOrFail($id);
            if ($actualite->image) {
                Storage::disk('public')->delete($actualite->image);
            }
            $actualite->delete();
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur suppression actualité : ' . $e->getMessage()
            ], 500);
        }
    }
}
