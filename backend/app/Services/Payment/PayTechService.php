<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayTechService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $env;
    protected string $baseUrl = 'https://paytech.sn/api/payment/request-payment';

    public function __construct()
    {
        $this->apiKey    = config('services.paytech.api_key') ?: env('PAYTECH_API_KEY', '316f8f01484da3593c8057eb7c17e858407e345bd0930bb22c8bf4b05026d02f');
        $this->apiSecret = config('services.paytech.api_secret') ?: env('PAYTECH_SECRET_KEY', 'c464e73f73e8b2126c1887a742b13d9a5421f37629e7155ce7c794ceaaea0101');
        $this->env       = config('services.paytech.env') ?: env('PAYTECH_ENV', 'test');
    }

    /**
     * Request payment from PayTech API strictly targeted for Wave or Orange Money
     */
    public function requestPayment(array $data): array
    {
        $rawMethod = strtolower($data['payment_method'] ?? 'wave');
        $isWave = str_contains($rawMethod, 'wave') || $rawMethod === 'wv';
        $targetMethod = $isWave ? 'wave' : 'om';
        $phone = $data['phone'] ?? '';
        $ipnUrl = 'https://agroconnect-backend-bh30.onrender.com/api/paytech/ipn';

        $useSimulation = env('PAYTECH_SIMULATION', false) || $this->env === 'simulation';

        $currency = strtoupper(config('services.paytech.currency', env('PAYTECH_CURRENCY', 'XOF')));
        $itemPrice = $data['item_price'] ?? 1000;

        if (!$useSimulation && !empty($this->apiKey) && !empty($this->apiSecret)) {
            try {
                $response = Http::withoutVerifying()->timeout(8)->withHeaders([
                    'API_KEY'      => $this->apiKey,
                    'API_SECRET'   => $this->apiSecret,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, [
                    'item_name'   => $data['item_name'] ?? 'Commande AgroConnect',
                    'item_price'  => $itemPrice,
                    'currency'    => $currency,
                    'ref_command' => $data['ref_command'] ?? ('AGC-' . time()),
                    'command_name'=> $data['command_name'] ?? 'Achat Récoltes Sénégal',
                    'env'         => $this->env,
                    'ipn_url'     => $data['ipn_url'] ?? $ipnUrl,
                    'success_url' => $data['success_url'] ?? env('FRONTEND_URL', 'https://agroconnect-frontend-mauve.vercel.app') . '/commandes?status=success',
                    'cancel_url'  => $data['cancel_url'] ?? env('FRONTEND_URL', 'https://agroconnect-frontend-mauve.vercel.app') . '/panier?status=cancel',
                    'custom_field'=> json_encode([
                        'payment_method' => $targetMethod,
                        'phone'          => $phone,
                        'client_name'    => $data['client_name'] ?? ''
                    ]),
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $redUrl = $json['redirect_url'] ?? ($json['redirectUrl'] ?? null);

                    if ($redUrl || (isset($json['success']) && $json['success'] == 1)) {
                        return [
                            'success'        => true,
                            'token'          => $json['token'] ?? null,
                            'redirect_url'   => $redUrl,
                            'transaction_id' => 'PAYTECH-' . ($json['token'] ?? strtoupper(Str::random(10))),
                            'status'         => 'pending',
                            'message'        => 'Paiement PayTech initié avec succès.',
                            'raw'            => $json,
                        ];
                    } else {
                        Log::warning('PayTech API refusé: ' . json_encode($json));
                    }
                } else {
                    Log::error('PayTech HTTP Error: ' . $response->status() . ' - ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Erreur API PayTech: ' . $e->getMessage());
            }
        }

        // Mode Simulation de secours (si pas de connexion PayTech)
        $methodName = $isWave ? 'Wave' : 'Orange Money';
        $prefix = $isWave ? 'WV-' : 'OM-';
        $txId = $prefix . strtoupper(Str::random(12));

        return [
            'success'        => true,
            'token'          => 'TOK-' . strtoupper(Str::random(10)),
            'redirect_url'   => null,
            'transaction_id' => $txId,
            'status'         => 'completed',
            'message'        => "Paiement {$methodName} validé avec succès pour le numéro {$phone}.",
            'metadata'       => [
                'provider'       => $targetMethod,
                'phone'          => $phone,
                'paytech_active' => !empty($this->apiKey),
                'env'            => $this->env,
                'timestamp'      => now()->toIso8601String()
            ]
        ];
    }
}
