<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Material\StoreMaterialRequest;
use App\Http\Requests\Material\UpdateMaterialRequest;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MaterialController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Material::query();
        
        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('current_stock', '<=', 'min_stock');
            } elseif ($request->stock_status === 'available') {
                $query->whereColumn('current_stock', '>', 'min_stock');
            } elseif ($request->stock_status === 'empty') {
                $query->where('current_stock', '<=', 0);
            }
        }
        
        // Filter by minimum stock level
        if ($request->filled('max_stock')) {
            $query->where('current_stock', '<=', (int) $request->max_stock);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $materials = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'filters' => $request->only(['search', 'stock_status', 'max_stock']),
        ]);
    }

    public function store(StoreMaterialRequest $request)
    {
        Material::create($request->validated());

        return redirect()->route('materials.index')
            ->with('success', 'Bahan baku berhasil ditambahkan!');
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $material->update($request->validated());

        return redirect()->route('materials.index')
            ->with('success', 'Bahan baku berhasil diperbarui!');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Bahan baku berhasil dihapus!');
    }
}
