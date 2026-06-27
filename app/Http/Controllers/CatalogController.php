<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Catalog\FilterProductRequest;
use App\Models\Gallery;
use App\Models\Product;
use App\Services\ProductPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CatalogController extends Controller
{
    protected ProductPriceService $priceService;

    public function __construct(ProductPriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function index(): InertiaResponse
    {
        $products  = Product::latest()->get();
        $galleries = Gallery::orderBy('sort_order')->get();
        $user      = auth()->user();

        $products = $products->map(function ($product) use ($user) {
            $product->pricing             = $this->priceService->getProductPrice($product, $user);
            $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
            return $product;
        });

        return Inertia::render('CatalogPage', [
            'products'  => $products,
            'galleries' => $galleries,
            'settings'  => config('settings'),
        ]);
    }

    public function shop(): InertiaResponse
    {
        $products  = Product::latest()->get();
        $user      = auth()->user();

        $products = $products->map(function ($product) use ($user) {
            $product->pricing             = $this->priceService->getProductPrice($product, $user);
            $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
            return $product;
        });

        return Inertia::render('ShopPage', [
            'products' => $products,
            'settings' => config('settings'),
        ]);
    }

    /**
     * HTMX/JSON filter endpoint — returns JSON for Vue's axios call.
     */
    public function filter(FilterProductRequest $request): JsonResponse|RedirectResponse
    {
        if (! $request->wantsJson() && ! $request->header('HX-Request')) {
            return redirect()->route('catalog.index');
        }

        $search = trim((string) ($request->validated('search') ?? ''));
        $stock  = $request->validated('stock') ?? 'semua';
        $query  = Product::query()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($stock === 'tersedia') {
            $query->where('current_stock', '>', 0);
        } elseif ($stock === 'habis') {
            $query->where('current_stock', '<=', 0);
        }

        $products = $query->get();
        $user     = auth()->user();

        $products = $products->map(function ($product) use ($user) {
            $product->pricing             = $this->priceService->getProductPrice($product, $user);
            $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
            return $product;
        });

        return response()->json(['products' => $products]);
    }
}
