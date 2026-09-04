<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KasirXenditWebhookController extends Controller
{
    protected XenditService $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Endpoint Webhook Callback Xendit / Central Router (Uang Masuk Kasir).
     */
    public function handleCallback(Request $request)
    {
        Log::info('[KasirXenditWebhookController] Callback received: ', $request->all());

        $incomingToken = $request->header('x-callback-token');
        $usaha = Usaha::latest()->first();

        if (!$usaha) {
            return response()->json(['status' => 'error', 'message' => 'Shop configuration not found.'], 404);
        }

        $result = $this->xenditService->handleWebhook($request->all(), $incomingToken, $usaha);

        return response()->json($result, 200);
    }
}
