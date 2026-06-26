<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Catalog\FilterProductRequest;
use App\Models\Gallery;
use App\Models\Product;
use App\Services\ProductPriceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogController extends Controller
{
    protected ProductPriceService $priceService;

    public function __construct(ProductPriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function index(): View
    {
        $products  = Product::latest()->get();
        $galleries = Gallery::orderBy('sort_order')->get();
        $user = auth()->user();
        
        // Add pricing info to each product
        $products = $products->map(function ($product) use ($user) {
            $product->pricing = $this->priceService->getProductPrice($product, $user);
            $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
            return $product;
        });
        
        return view('catalog', compact('products', 'galleries'));
    }

    public function filter(FilterProductRequest $request): View|RedirectResponse
    {
        if (! $request->header('HX-Request')) {
            return redirect()->route('catalog.index');
        }

        $search = trim((string) ($request->validated('search') ?? ''));
        $stock  = $request->validated('stock') ?? 'semua';

        $query = Product::query()->latest();

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
        $user = auth()->user();
        
        // Add pricing info to each product
        $products = $products->map(function ($product) use ($user) {
            $product->pricing = $this->priceService->getProductPrice($product, $user);
            $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
            return $product;
        });

        return view('catalog.products-grid', compact('products'));
    }
}
