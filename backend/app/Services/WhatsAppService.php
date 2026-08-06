<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiKey;
    protected string $instanceId;
    protected string $supportNumber;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.wossap.api_key', env('WOSSAP_API_KEY', ''));
        $this->instanceId = config('services.wossap.instance_id', env('WOSSAP_INSTANCE_ID', ''));
        $this->supportNumber = config('services.wossap.support_number', env('WHATSAPP_SUPPORT_NUMBER', '221765512974'));
        $this->baseUrl = config('services.wossap.base_url', env('WOSSAP_BASE_URL', 'https://wossap.ai/api/v1'));
    }

    /**
     * Génère un lien wa.me formaté avec texte encodé pour WhatsApp.
     */
    public function genererLienWhatsApp(string $telephone, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $telephone);
        if (!str_starts_with($cleanPhone, '221') && strlen($cleanPhone) === 9) {
            $cleanPhone = '221' . $cleanPhone;
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
    }

    /**
     * Envoie un message texte directement via l'API Wossap.ai (https://wossap.ai).
     */
    public function envoyerMessageDirect(string $destinataire, string $texte): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $destinataire);
        if (!str_starts_with($cleanPhone, '221') && strlen($cleanPhone) === 9) {
            $cleanPhone = '221' . $cleanPhone;
        }

        if (empty($this->apiKey)) {
            Log::info("Wossap.ai API (Mode Simulation) vers {$cleanPhone} : {$texte}");
            return [
                'success'  => true,
                'provider' => 'wossap.ai',
                'mode'     => 'simulation',
                'link'     => $this->genererLienWhatsApp($cleanPhone, $texte),
                'message'  => 'Message Wossap.ai préparé avec succès.'
            ];
        }

        try {
            // Envoi HTTP direct vers la passerelle Wossap.ai API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ])->post("{$this->baseUrl}/messages/send", [
                'instance_id' => $this->instanceId,
                'number'      => $cleanPhone,
                'message'     => $texte,
                'type'        => 'text'
            ]);

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'provider' => 'wossap.ai',
                    'data'     => $response->json()
                ];
            }

            Log::error('Erreur API Wossap.ai: ' . $response->body());
            return [
                'success'  => false,
                'provider' => 'wossap.ai',
                'error'    => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Exception Wossap.ai API: ' . $e->getMessage());
            return [
                'success'  => false,
                'provider' => 'wossap.ai',
                'error'    => $e->getMessage()
            ];
        }
    }

    /**
     * Formate un message WhatsApp de confirmation de commande pour le client ou producteur.
     */
    public function formerMessageCommande(array $commandeData): string
    {
        $id = $commandeData['id'] ?? rand(1000, 9999);
        $client = $commandeData['client_nom'] ?? 'Client';
        $total = number_format($commandeData['total'] ?? 0, 0, ',', ' ');
        $adresse = $commandeData['adresse'] ?? 'Dakar, Sénégal';
        $methodePaiement = strtoupper($commandeData['paiement'] ?? 'CASH');

        $msg = "🌿 *AGROCONNECT SÉNÉGAL (WOSSAP.AI)* 🌾\n";
        $msg .= "-------------------------------------\n";
        $msg .= "📦 *Commande #CMD-{$id}*\n";
        $msg .= "👤 *Client :* {$client}\n";
        $msg .= "📍 *Adresse :* {$adresse}\n";
        $msg .= "💳 *Paiement :* {$methodePaiement}\n";
        $msg .= "-------------------------------------\n";

        if (!empty($commandeData['articles']) && is_array($commandeData['articles'])) {
            $msg .= "🛒 *Produits commandés :*\n";
            foreach ($commandeData['articles'] as $art) {
                $nom = $art['nom'] ?? 'Produit';
                $qte = $art['quantite'] ?? 1;
                $unite = $art['unite'] ?? 'kg';
                $prix = number_format($art['prix'] ?? 0, 0, ',', ' ');
                $msg .= "• {$qte} {$unite} de {$nom} ({$prix} FCFA)\n";
            }
            $msg .= "-------------------------------------\n";
        }

        $msg .= "💰 *Montant Total : {$total} FCFA*\n\n";
        $msg .= "Merci de faire confiance à l'agriculture locale sénégalaise ! 🇸🇳";

        return $msg;
    }
}
