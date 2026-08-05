<?php

namespace App\Services\Payment\Gateways;

use App\Models\Commande;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Str;

class CashGateway implements PaymentGatewayInterface
{
    public function process(Commande $commande, array $details = []): array
    {
        // Pour le paiement en espèces à la livraison, générer un identifiant unique CASH
        $transactionId = 'CASH-' . strtoupper(Str::random(10));

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'pending',
            'message'        => 'Commande enregistrée en espèces à la livraison. Le paiement se fera à la réception auprès du livreur.',
            'metadata'       => [
                'provider'  => 'cash',
                'api_mock'  => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
