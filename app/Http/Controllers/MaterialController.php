<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Material\StoreMaterialRequest;
use App\Http\Requests\Material\UpdateMaterialRequest;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
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
        
        $materials = $query->orderBy('name')->paginate(15);

        if ($request->header('HX-Target') === 'materials-list') {
            return view('materials.materials-list', compact('materials'));
        }
        return view('materials.materials-index', compact('materials'));
    }

    public function create(): View
    {
        $material = new Material();
        return view('materials.materials-form', compact('material'));
    }

    public function store(StoreMaterialRequest $request)
    {
        Material::create($request->validated());

        return response()
            ->view('materials.materials-list', ['materials' => Material::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Bahan baku berhasil ditambahkan!', 'type' => 'success'],
            ]));
    }

    public function edit(Material $material): View
    {
        return view('materials.materials-form', compact('material'));
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $material->update($request->validated());

        return response()
            ->view('materials.materials-list', ['materials' => Material::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Bahan baku berhasil diperbarui!', 'type' => 'success'],
            ]));
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()
            ->view('materials.materials-list', ['materials' => Material::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'toast' => ['message' => 'Bahan baku berhasil dihapus!', 'type' => 'success'],
            ]));
    }
}
