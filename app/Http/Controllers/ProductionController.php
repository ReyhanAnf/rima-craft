<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordProductionAction;
use App\Http\Requests\Production\StoreProductionRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\Production;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductionController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Production::with(['materials.material', 'results.product', 'artisanWages.artisan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('results.product', function ($q) use ($search): void {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $productions = $query->orderByDesc('date')->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Productions/Index', [
            'productions' => $productions,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): InertiaResponse
    {
        $materials = Material::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $artisans = User::whereHas('roles', fn ($query) => $query->where('name', 'pengrajin'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Productions/Form', [
            'materials' => $materials,
            'products' => $products,
            'artisans' => $artisans,
        ]);
    }

    public function store(StoreProductionRequest $request)
    {
        try {
            (new RecordProductionAction)->handle($request->validated());

            return redirect()->route('productions.index')
                             ->with('success', 'Proses produksi berhasil dicatat! Stok bahan baku terpotong dan produk jadi bertambah.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Production $production): InertiaResponse
    {
        $production->load(['materials.material', 'results.product', 'artisanWages.artisan']);
        return Inertia::render('Productions/Show', [
            'production' => $production,
        ]);
    }
}
