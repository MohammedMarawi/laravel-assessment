<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;


class WebhookController extends Controller
{
    
    protected PaymentService $paymentService;

    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        // Note: No auth middleware - webhooks must be accessible without authentication
    }

    
    public function handle(Request $request)
    {
        // Get raw payload for signature verification
        // Note: getContent() is used instead of all() for signature verification
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // Log incoming webhook for debugging
        Log::channel('stripe')->info('Webhook received', [
            'ip' => $request->ip(),
            'has_signature' => !empty($signature),
        ]);

        // Validate that signature header is present
        if (empty($signature)) {
            Log::channel('stripe')->warning('Webhook received without signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Missing Stripe-Signature header',
            ], 400);
        }

        try {
            // Delegate to PaymentService for signature verification and event handling
            $result = $this->paymentService->handleWebhook($payload, $signature);

            Log::channel('stripe')->info('Webhook processed successfully', [
                'event_type' => $result['event_type'] ?? 'unknown',
                'event_id' => $result['event_id'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'event_type' => $result['event_type'] ?? null,
            ]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature - possible tampering or misconfiguration
            Log::channel('stripe')->error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid signature',
            ], 400);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::channel('stripe')->error('Webhook payload invalid', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
            ], 400);
        } catch (\Exception $e) {
            // General error during processing
            Log::channel('stripe')->error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
