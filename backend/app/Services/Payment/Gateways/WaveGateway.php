<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PayTechService;

class WaveGateway implements PaymentGatewayInterface
{
    protected PayTechService $payTechService;

    public function __construct()
    {
        $this->payTechService = new PayTechService();
    }

    public function process(Commande $commande, array $details = []): array
    {
        $phone = $details['telephone'] ?? $commande->telephone;

        return $this->payTechService->requestPayment([
            'item_name'      => "Commande AgroConnect #AGC-{$commande->id}",
            'item_price'     => $commande->montant_total,
            'ref_command'    => "AGC-{$commande->id}",
            'command_name'   => "Paiement Wave Récoltes Sénégal (#AGC-{$commande->id})",
            'payment_method' => 'wave',
            'phone'          => $phone,
            'client_name'    => $commande->client ? $commande->client->name : 'Client AgroConnect',
        ]);
    }
}
