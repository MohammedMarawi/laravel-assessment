<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by minimum price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Filter by maximum price
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by creation date
        if ($request->filled('created_after')) {
            $query->whereDate('created_at', '>=', $request->created_after);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $products = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'status'  => 'success',
            'message' => 'Products retrieved successfully.',
            'data'    => ProductResource::collection($products),
            'meta'    => [
                'total'     => $products->total(),
                'page'      => $products->currentPage(),
                'per_page'  => $products->perPage(),
            ]
        ]);
    }

    
    public function store(StoreProductRequest $request)
    {
        // Get validated data from Form Request
        $validated = $request->validated();

        // Create the product
        $product = Product::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'status'      => $validated['status'],
        ]);

        // Upload file using Spatie MediaLibrary
        if ($request->hasFile('file')) {
            $product->addMedia($request->file('file'))
                ->toMediaCollection('products');
        }

        Log::info("Product created: {$product->id} by user {$request->user()->id}");

        return response()->json([
            'status'  => 'success',
            'message' => 'Product created successfully.',
            'data'    => new ProductResource($product)
        ], 201);
    }

    
    public function show(Product $product)
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Product retrieved successfully.',
            'data'    => new ProductResource($product)
        ]);
    }

    
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Get validated data from Form Request
        $validated = $request->validated();

        // Update product data (only update fields that are present)
        $product->update([
            'title'       => $validated['title'] ?? $product->title,
            'description' => $validated['description'] ?? $product->description,
            'price'       => $validated['price'] ?? $product->price,
            'status'      => $validated['status'] ?? $product->status,
        ]);

        // Replace file if new one uploaded
        if ($request->hasFile('file')) {
            // Delete old media
            $product->clearMediaCollection('products');

            // Add new media
            $product->addMedia($request->file('file'))
                ->toMediaCollection('products');
        }

        Log::info("Product updated: {$product->id} by user {$request->user()->id}");

        return response()->json([
            'status'  => 'success',
            'message' => 'Product updated successfully.',
            'data'    => new ProductResource($product)
        ]);
    }

    
    public function destroy(Product $product)
    {
        // Clear all media associated with this product
        $product->clearMediaCollection('products');

        // Delete the product (soft delete if enabled)
        $product->delete();

        Log::info("Product deleted: {$product->id} by user " . Auth::id());

        return response()->json([
            'status'  => 'success',
            'message' => 'Product deleted successfully.',
            'data'    => null
        ]);
    }
}
