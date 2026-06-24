<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordProductionAction;
use App\Http\Requests\Production\StoreProductionRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\Production;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Production::with(['materials.material', 'results.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('results.product', function ($q) use ($search): void {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $productions = $query->orderByDesc('date')->orderByDesc('id')->paginate(10);

        if ($request->header('HX-Target') === 'productions-list') {
            return view('productions.productions-list', compact('productions'));
        }

        return view('productions.productions-index', compact('productions'));
    }

    public function create(): View
    {
        $materials = Material::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('productions.productions-form', compact('materials', 'products'));
    }

    public function store(StoreProductionRequest $request)
    {
        try {
            (new RecordProductionAction)->handle($request->validated());

            return redirect()->route('productions.index')
                             ->with('toast', ['message' => 'Proses produksi berhasil dicatat! Stok bahan baku terpotong dan produk jadi bertambah.', 'type' => 'success']);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Production $production): View
    {
        $production->load(['materials.material', 'results.product']);
        return view('productions.productions-show', compact('production'));
    }
}
