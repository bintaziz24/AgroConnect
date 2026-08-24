<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discussion;
use App\Models\Message;
use App\Models\Agriculteur;
use App\Models\Produit;
use App\Models\Commande;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscussionController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Obtenir toutes les discussions de l'utilisateur connecté (Client, Agriculteur, Livreur ou Admin).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Discussion::with([
            'client',
            'agriculteur.user',
            'livreur',
            'produit',
            'commande',
            'messages' => function ($q) {
                $q->orderBy('created_at', 'asc')->with('expediteur');
            },
            'dernierMessage'
        ]);

        if ($user->role === 'admin') {
            // L'administrateur peut voir toutes les conversations
        } elseif ($user->role === 'livreur') {
            $query->where('livreur_id', $user->id);
        } elseif ($user->role === 'agriculteur' || $user->agriculteur) {
            $agriId = $user->agriculteur ? $user->agriculteur->id : null;
            if ($agriId) {
                $query->where('agriculteur_id', $agriId);
            } else {
                $query->whereHas('agriculteur', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        } else {
            $query->where('client_id', $user->id);
        }

        $discussions = $query->orderByDesc('dernier_message_at')->get();

        // Calculer le nombre de messages non lus et dédoublonner pour chaque discussion
        $discussions->transform(function ($discussion) use ($user) {
            $nonLus = Message::where('discussion_id', $discussion->id)
                ->where('expediteur_id', '!=', $user->id)
                ->where('est_lu', false)
                ->count();

            $discussion->non_lus_count = $nonLus;

            if ($discussion->relationLoaded('messages')) {
                $seen = [];
                $uniqueMessages = [];
                foreach ($discussion->messages as $m) {
                    $text = trim($m->contenu ?? '');
                    $isAutoGreeting = str_contains($text, 'je suis intéressé par votre produit') ||
                                     str_contains($text, 'question concernant ma commande') ||
                                     str_contains($text, 'livreur en charge de la livraison');
                    $key = $isAutoGreeting ? ($m->expediteur_id . '_' . $text) : ('id_' . $m->id);
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $uniqueMessages[] = $m;
                    }
                }
                $discussion->setRelation('messages', collect($uniqueMessages));
            }

            return $discussion;
        });

        return response()->json($discussions);
    }

    /**
     * Démarrer ou récupérer une discussion entre un client, un agriculteur ou un livreur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agriculteur_id' => 'required',
            'livreur_id'     => 'nullable|exists:users,id',
            'produit_id'     => 'nullable|exists:produits,id',
            'commande_id'    => 'nullable|exists:commandes,id',
            'message'        => 'nullable|string|max:2000',
            'type_message'   => 'nullable|in:texte,image,fichier,systeme',
            'fichier_url'    => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $agriculteur = Agriculteur::with('user')->find($request->agriculteur_id);
        if (!$agriculteur) {
            $agriculteur = Agriculteur::with('user')->where('user_id', $request->agriculteur_id)->first();
        }

        if (!$agriculteur) {
            return response()->json(['error' => 'Agriculteur introuvable.'], 404);
        }

        // Éviter qu'un agriculteur ouvre une discussion avec lui-même
        if ($agriculteur->user_id === $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas démarrer une discussion avec vous-même.'], 422);
        }

        // Chercher s'il existe déjà une discussion similaire
        $discussion = Discussion::where('client_id', $user->id)
            ->where('agriculteur_id', $agriculteur->id)
            ->when($request->produit_id, function ($q, $pId) {
                return $q->where('produit_id', $pId);
            })
            ->when($request->commande_id, function ($q, $cId) {
                return $q->where('commande_id', $cId);
            })
            ->first();

        $isNew = false;
        if (!$discussion) {
            $discussion = Discussion::create([
                'client_id'          => $user->id,
                'agriculteur_id'     => $agriculteur->id,
                'livreur_id'         => $request->livreur_id,
                'produit_id'         => $request->produit_id,
                'commande_id'        => $request->commande_id,
                'statut'             => 'active',
                'dernier_message_at' => now(),
            ]);
            $isNew = true;
        }

        // Si c'est une toute NOUVELLE discussion et qu'un message initial est fourni, l'enregistrer
        if ($isNew && (!empty($request->message) || !empty($request->fichier_url))) {
            $msg = Message::create([
                'discussion_id' => $discussion->id,
                'expediteur_id' => $user->id,
                'contenu'       => $request->message ?? '',
                'type_message'   => $request->type_message ?? 'texte',
                'fichier_url'    => $request->fichier_url,
                'est_lu'        => false,
            ]);

            $discussion->update(['dernier_message_at' => now()]);

            // Envoyer une alerte WhatsApp à l'agriculteur
            $telephoneTarget = $agriculteur->user->telephone ?? null;
            if ($telephoneTarget) {
                $nomProduit = $discussion->produit ? $discussion->produit->nom : 'un produit';
                $texteNotif = "💬 *AGROCONNECT - NOUVEAU MESSAGE*\n\n" .
                             "Bonjour {$agriculteur->user->name},\n" .
                             "Le client *{$user->name}* vous a envoyé un message au sujet de *{$nomProduit}* :\n\n" .
                             "« " . ($request->message ?? 'Pièce jointe reçue') . " »\n\n" .
                             "Connectez-vous à votre espace AgroConnect pour y répondre.";
                
                $this->whatsAppService->envoyerMessageDirect($telephoneTarget, $texteNotif);
            }
        }

        return response()->json(
            $discussion->load([
                'client',
                'agriculteur.user',
                'livreur',
                'produit',
                'commande',
                'messages.expediteur'
            ]),
            201
        );
    }

    /**
     * Afficher le détail d'une discussion et marquer les messages reçus comme lus.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $discussion = Discussion::with([
            'client',
            'agriculteur.user',
            'livreur',
            'produit',
            'commande',
            'messages' => function ($q) {
                $q->orderBy('created_at', 'asc')->with('expediteur');
            }
        ])->findOrFail($id);

        // Vérifier l'accès
        $isClient = $discussion->client_id === $user->id;
        $isAgriculteur = ($discussion->agriculteur && $discussion->agriculteur->user_id === $user->id) || ($user->agriculteur && $discussion->agriculteur_id === $user->agriculteur->id);
        $isLivreur = $discussion->livreur_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isClient && !$isAgriculteur && !$isLivreur && !$isAdmin) {
            return response()->json(['error' => 'Accès non autorisé à cette discussion.'], 403);
        }

        // Marquer les messages reçus comme lus
        Message::where('discussion_id', $discussion->id)
            ->where('expediteur_id', '!=', $user->id)
            ->where('est_lu', false)
            ->update(['est_lu' => true]);

        // Dédoublonner les messages automatiques répétés envoyés précédemment
        if ($discussion->relationLoaded('messages')) {
            $seen = [];
            $uniqueMessages = [];
            foreach ($discussion->messages as $m) {
                $text = trim($m->contenu ?? '');
                $isAutoGreeting = str_contains($text, 'je suis intéressé par votre produit') ||
                                 str_contains($text, 'question concernant ma commande') ||
                                 str_contains($text, 'livreur en charge de la livraison');
                $key = $isAutoGreeting ? ($m->expediteur_id . '_' . $text) : ('id_' . $m->id);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniqueMessages[] = $m;
                }
            }
            $discussion->setRelation('messages', collect($uniqueMessages));
        }

        return response()->json($discussion);
    }

    /**
     * Envoyer un nouveau message dans la discussion.
     */
    public function envoyerMessage(Request $request, $id)
    {
        $request->validate([
            'contenu'      => 'nullable|string|max:2000',
            'type_message' => 'nullable|in:texte,image,fichier,systeme',
            'fichier_url'  => 'nullable|string|max:1000',
        ]);

        if (empty($request->contenu) && empty($request->fichier_url)) {
            return response()->json(['error' => 'Le message ne peut pas être vide.'], 422);
        }

        $user = $request->user();
        $discussion = Discussion::with(['client', 'agriculteur.user', 'livreur', 'produit'])->findOrFail($id);

        // Vérifier l'accès
        $isClient = $discussion->client_id === $user->id;
        $isAgriculteur = ($discussion->agriculteur && $discussion->agriculteur->user_id === $user->id) || ($user->agriculteur && $discussion->agriculteur_id === $user->agriculteur->id);
        $isLivreur = $discussion->livreur_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isClient && !$isAgriculteur && !$isLivreur && !$isAdmin) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        // Créer le message
        $message = Message::create([
            'discussion_id' => $discussion->id,
            'expediteur_id' => $user->id,
            'contenu'       => $request->contenu ?? '',
            'type_message'   => $request->type_message ?? 'texte',
            'fichier_url'    => $request->fichier_url,
            'est_lu'        => false,
        ]);

        $discussion->update(['dernier_message_at' => now()]);

        // Déterminer le destinataire pour la notification WhatsApp
        $destinataireUser = null;
        if ($isClient) {
            $destinataireUser = $discussion->agriculteur->user ?? null;
        } else {
            $destinataireUser = $discussion->client;
        }

        if ($destinataireUser && !empty($destinataireUser->telephone)) {
            $expediteurNom = $user->name;
            $sujet = $discussion->produit ? $discussion->produit->nom : 'AgroConnect';
            $contenuApercu = $request->fichier_url ? '📷 Pièce jointe reçue' : "« {$request->contenu} »";
            $texteNotif = "💬 *AGROCONNECT - NOUVEAU MESSAGE*\n\n" .
                         "Bonjour {$destinataireUser->name},\n" .
                         "*{$expediteurNom}* vous a envoyé un message ({$sujet}) :\n\n" .
                         "{$contenuApercu}\n\n" .
                         "Répondez directement sur votre espace AgroConnect.";

            $this->whatsAppService->envoyerMessageDirect($destinataireUser->telephone, $texteNotif);
        }

        return response()->json($message->load('expediteur:id,name,role'), 201);
    }

    /**
     * Nombre total de messages non lus pour l'utilisateur connecté.
     */
    public function compterNonLus(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $discussionIds = Discussion::pluck('id');
        } elseif ($user->role === 'livreur') {
            $discussionIds = Discussion::where('livreur_id', $user->id)->pluck('id');
        } elseif ($user->role === 'agriculteur' || $user->agriculteur) {
            $agriId = $user->agriculteur ? $user->agriculteur->id : null;
            if ($agriId) {
                $discussionIds = Discussion::where('agriculteur_id', $agriId)->pluck('id');
            } else {
                $discussionIds = Discussion::whereHas('agriculteur', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pluck('id');
            }
        } else {
            $discussionIds = Discussion::where('client_id', $user->id)->pluck('id');
        }

        $totalNonLus = Message::whereIn('discussion_id', $discussionIds)
            ->where('expediteur_id', '!=', $user->id)
            ->where('est_lu', false)
            ->count();

        return response()->json(['non_lus' => $totalNonLus]);
    }
}
