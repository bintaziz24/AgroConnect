<?php

namespace App\Services\Payment;

use App\Models\Commande;

interface PaymentGatewayInterface
{
    /**
     * Process the payment for a given order.
     *
     * @param Commande $commande
     * @param array $details
     * @return array [success => bool, transaction_id => string, message => string, status => string]
     */
    public function process(Commande $commande, array $details = []): array;
}
