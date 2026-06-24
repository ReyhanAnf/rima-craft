<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Catalog\FilterProductRequest;
use App\Models\Gallery;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        $products  = Product::latest()->get();
        $galleries = Gallery::orderBy('sort_order')->get();
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

        return view('catalog.products-grid', compact('products'));
    }
}
