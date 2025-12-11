<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCheckoutRequest;
use App\Services\PaymentService;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PaymentResource;


class PaymentController extends Controller
{
    
    protected PaymentService $paymentService;

    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        // Apply auth middleware except for public callback routes
        $this->middleware('auth:sanctum')->except(['success', 'cancel']);
    }

    
    public function createCheckout(CreateCheckoutRequest $request)
    {
        try {
            // Get validated data from Form Request
            $validated = $request->validated();

            $subscription = Subscription::findOrFail($validated['subscription_id']);

            // Verify the user owns this subscription
            if ($subscription->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access to subscription.',
                    'data' => null,
                ], 403);
            }

            // Prevent checkout for already active subscriptions
            if ($subscription->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Subscription is already active.',
                    'data' => null,
                ], 400);
            }

            $checkoutData = $this->paymentService->createCheckoutSession(
                $subscription,
                $validated
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Checkout session created successfully.',
                'data' => $checkoutData,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create checkout session', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session ID is required.',
                'data' => null,
            ], 400);
        }

        try {
            $payment = Payment::where('stripe_session_id', $sessionId)->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found.',
                    'data' => null,
                ], 404);
            }

            // Fallback: If payment is still unpaid (webhook hasn't arrived yet),
            // process the payment directly by verifying with Stripe.
            // This handles localhost development where webhooks can't reach the server.
            if ($payment->status === 'unpaid') {
                Log::info('Processing payment via success endpoint fallback', [
                    'session_id' => $sessionId,
                    'payment_id' => $payment->id,
                ]);
                $this->paymentService->handleSuccessfulPayment($sessionId);
                $payment->refresh();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment completed successfully!',
                'data' => new PaymentResource($payment->load('subscription.product')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve payment information.',
                'data' => null,
            ], 500);
        }
    }

    
    public function cancel(Request $request)
    {
        return response()->json([
            'status' => 'cancelled',
            'message' => 'Payment was cancelled. You can try again anytime.',
            'data' => null,
        ]);
    }

    
    public function index(Request $request)
    {
        try {
            $payments = $request->user()
                ->payments()
                ->with('subscription.product')
                ->latest()
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'status' => 'success',
                'message' => 'Payments retrieved successfully.',
                'data' => PaymentResource::collection($payments),
                'meta' => [
                    'total' => $payments->total(),
                    'page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve payments.',
                'data' => null,
            ], 500);
        }
    }
}
