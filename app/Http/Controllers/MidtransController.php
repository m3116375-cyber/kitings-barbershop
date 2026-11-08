<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    /**
     * Webhook "Payment Notification URL" dari Midtrans.
     * Diatur di Dashboard Midtrans > Settings > Payment > Notification URL:
     *   https://DOMAIN-ANDA/midtrans/notification
     */
    public function notification(Request $request, MidtransService $midtrans)
    {
        $data = $request->all();

        if (! $midtrans->verifySignature($data)) {
            Log::warning('Midtrans: signature tidak valid', ['order_id' => $data['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payment = Payment::where('midtrans_order_id', $data['order_id'] ?? '')->first();
        if (! $payment) {
            // Midtrans mengirim "test notification" dengan order_id acak saat tombol Test ditekan.
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ((int) ($data['gross_amount'] ?? 0) !== (int) $payment->amount) {
            Log::warning('Midtrans: nominal tidak cocok', ['order_id' => $data['order_id']]);

            return response()->json(['message' => 'Amount mismatch'], 422);
        }

        $midtrans->applyStatus($payment, $data);

        return response()->json(['message' => 'OK']);
    }
}
