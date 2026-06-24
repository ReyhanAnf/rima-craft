<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AdjustStockAction;
use App\Http\Requests\StockAdjustment\StoreStockAdjustmentRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
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
        
        $adjustments = $query->latest()->paginate(15);

        if ($request->header('HX-Target') === 'stock-adjustments-list') {
            return view('stock-adjustments.stock-adjustments-list', compact('adjustments'));
        }

        return view('stock-adjustments.stock-adjustments-index', compact('adjustments'));
    }

    public function create()
    {
        $materials = Material::select('id', 'name', 'current_stock', 'unit')->get();
        $products = Product::select('id', 'name', 'current_stock')->get();

        return view('stock-adjustments.stock-adjustments-form', compact('materials', 'products'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        try {
            (new AdjustStockAction)->handle($request->validated());

            $adjustments = StockAdjustment::with(['adjustable', 'user'])->latest()->paginate(15);

            return response()
                ->view('stock-adjustments.stock-adjustments-list', compact('adjustments'))
                ->header('HX-Trigger', 'close-drawer');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
