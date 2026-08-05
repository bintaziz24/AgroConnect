<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Str;

class FreeMoneyGateway implements PaymentGatewayInterface
{
    public function process(Commande $commande, array $details = []): array
    {
        // Simulation d'un appel API Free Money (Free API)
        
        $telephone = $details['telephone'] ?? $commande->telephone;
        
        // Simuler le succès du paiement Free
        $transactionId = 'FM-' . strtoupper(Str::random(12));

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'completed',
            'message'        => 'Paiement Free Money traité avec succès pour le numéro : ' . $telephone,
            'metadata'       => [
                'provider' => 'free_money',
                'phone'    => $telephone,
                'api_mock' => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
