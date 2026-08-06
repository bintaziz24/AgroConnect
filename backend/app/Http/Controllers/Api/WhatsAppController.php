<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Obtenir la configuration WhatsApp et le lien de contact direct.
     */
    public function config(Request $request)
    {
        $supportNumber = env('WHATSAPP_SUPPORT_NUMBER', '221765512974');
        $defautMsg = "Bonjour AgroConnect ! Je souhaite obtenir des informations sur vos produits agricoles frais.";
        $link = $this->whatsAppService->genererLienWhatsApp($supportNumber, $defautMsg);

        return response()->json([
            'support_number' => $supportNumber,
            'whatsapp_link'  => $link,
            'statut_api'     => !empty(env('WOSSAP_API_KEY')) ? 'actif' : 'simulation',
        ]);
    }

    /**
     * Générer un lien WhatsApp pour une commande spécifique.
     */
    public function lienCommande(Request $request)
    {
        $request->validate([
            'total'      => 'required|numeric',
            'articles'   => 'required|array',
            'client_nom' => 'nullable|string',
            'adresse'    => 'nullable|string',
            'paiement'   => 'nullable|string',
        ]);

        $message = $this->whatsAppService->formerMessageCommande($request->all());
        $supportNumber = env('WHATSAPP_SUPPORT_NUMBER', '221765512974');
        $link = $this->whatsAppService->genererLienWhatsApp($supportNumber, $message);

        return response()->json([
            'success' => true,
            'link'    => $link,
            'message' => $message,
        ]);
    }

    /**
     * Envoyer une notification WhatsApp directe à un utilisateur.
     */
    public function envoyerNotification(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'message'   => 'required|string',
        ]);

        $res = $this->whatsAppService->envoyerMessageDirect($request->telephone, $request->message);
        return response()->json($res);
    }
}
