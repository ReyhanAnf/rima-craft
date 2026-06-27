<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AdjustStockAction;
use App\Http\Requests\StockAdjustment\StoreStockAdjustmentRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = StockAdjustment::with(['adjustable', 'user']);
        
        // Filter by adjustment type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $adjustments = $query->latest()->paginate(15)->withQueryString();

        $materials = Material::select('id', 'name', 'current_stock', 'unit')->get();
        $products = Product::select('id', 'name', 'current_stock')->get();

        return Inertia::render('StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'materials' => $materials,
            'products' => $products,
            'filters' => $request->only(['type', 'date_from', 'date_to']),
        ]);
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        try {
            (new AdjustStockAction)->handle($request->validated());

            return redirect()->route('stock-adjustments.index')
                             ->with('success', 'Penyesuaian stok berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
