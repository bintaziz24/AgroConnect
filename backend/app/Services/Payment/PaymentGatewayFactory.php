<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\WaveGateway;
use App\Services\Payment\Gateways\OrangeMoneyGateway;
use App\Services\Payment\Gateways\FreeMoneyGateway;
use App\Services\Payment\Gateways\CashGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface
    {
        return match (strtolower(StrClean($method))) {
            'wave' => new WaveGateway(),
            'orange_money', 'orange-money', 'om' => new OrangeMoneyGateway(),
            'free_money', 'free-money', 'free' => new FreeMoneyGateway(),
            'cash', 'a la livraison', 'à la livraison' => new CashGateway(),
            default => throw new InvalidArgumentException("Le moyen de paiement '{$method}' n'est pas supporté."),
        };
    }
}

/**
 * Clean up method strings for match routing.
 */
function StrClean(string $str): string
{
    return str_replace(['-', ' '], '_', strtolower(trim($str)));
}
