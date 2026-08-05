<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commande;
use Illuminate\Support\Facades\Log;

class PayTechController extends Controller
{
    /**
     * IPN Webhook callback from PayTech Senegal
     */
    public function ipn(Request $request)
    {
        Log::info('IPN PayTech reçu:', $request->all());

        $type_event  = $request->input('type_event');
        $custom_field = json_decode($request->input('custom_field', '{}'), true);
        $ref_command  = $request->input('ref_command');

        if ($ref_command) {
            $commandeId = str_replace('AGC-', '', $ref_command);
            $commande   = Commande::find($commandeId);

            if ($commande) {
                if ($type_event === 'sale_complete' || $request->input('status') === 'success') {
                    $commande->statut = 'preparation';
                    $commande->save();

                    if ($commande->paiement) {
                        $commande->paiement->status = 'completed';
                        $commande->paiement->save();
                    }
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'IPN Traité avec succès']);
    }
}
