<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Exports\SubscriptionsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SubscriptionResource;
use Maatwebsite\Excel\Facades\Excel;


class SubscriptionController extends Controller
{
    
    protected SubscriptionService $subscriptionService;

    
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        // Apply authentication middleware to all controller methods
        $this->middleware('auth:sanctum');
    }

    
    public function index(Request $request)
    {
        try {
            // Authorize: Check if user can view any subscriptions
            $this->authorize('viewAny', Subscription::class);

            $filters = $request->only(['status', 'product_id']);

            $subscriptions = $this->subscriptionService->getUserSubscriptions(
                $request->user(),
                $filters
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Subscriptions retrieved successfully.',
                'data' => $subscriptions,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.',
                'data' => null,
            ], 403);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve subscriptions", [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve subscriptions.',
                'data' => null,
            ], 500);
        }
    }

    
    public function store(StoreSubscriptionRequest $request)
    {
        try {
            // Authorize: Check if user can create subscriptions
            $this->authorize('create', Subscription::class);

            // Get validated data from Form Request
            $validated = $request->validated();

            $subscription = $this->subscriptionService->createSubscription(
                $request->user(),
                $validated['product_id']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription created successfully. Please proceed to payment.',
                'data' => new SubscriptionResource($subscription->load('product')),
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You cannot create subscriptions.',
                'data' => null,
            ], 403);
        } catch (\Exception $e) {
            Log::error("Failed to create subscription", [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    
    public function show(Request $request, int $id)
    {
        try {
            // First, find the subscription
            $subscription = Subscription::with(['product', 'payments'])->findOrFail($id);

            // Authorize: Check if user can view this specific subscription
            $this->authorize('view', $subscription);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription retrieved successfully.',
                'data' => new SubscriptionResource($subscription),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You cannot view this subscription.',
                'data' => null,
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function cancel(Request $request, int $id)
    {
        try {
            // First, find the subscription
            $subscription = Subscription::findOrFail($id);

            // Authorize: Check if user can update (cancel) this subscription
            $this->authorize('update', $subscription);

            $this->subscriptionService->cancelSubscription($subscription);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription cancelled successfully.',
                'data' => $subscription->fresh(),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You cannot cancel this subscription.',
                'data' => null,
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            Log::error("Failed to cancel subscription", [
                'error' => $e->getMessage(),
                'subscription_id' => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    
    public function statistics(Request $request)
    {
        try {
            // Authorize: Check if user can view subscriptions (statistics is a viewAny action)
            $this->authorize('viewAny', Subscription::class);

            $stats = $this->subscriptionService->getUserStatistics($request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully.',
                'data' => $stats,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.',
                'data' => null,
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics.',
                'data' => null,
            ], 500);
        }
    }

    
    public function export(Request $request)
    {
        try {
            // Authorize: Check if user can view subscriptions
            $this->authorize('viewAny', Subscription::class);

            $filters = $request->only(['status', 'from_date', 'to_date']);

            // If not admin, only export user's own subscriptions
            if (!$request->user()->hasRole('admin')) {
                $filters['user_id'] = $request->user()->id;
            }

            $filename = 'subscriptions_' . now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(new SubscriptionsExport($filters), $filename);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.',
                'data' => null,
            ], 403);
        } catch (\Exception $e) {
            Log::error("Failed to export subscriptions", [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export subscriptions.',
                'data' => null,
            ], 500);
        }
    }
}
