<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRegionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Region::with(['parent', 'shippingRate']);
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        } else {
            $query->where('type', 'province');
        }

        $regions = $query->orderBy('name')->paginate(15)->withQueryString();
        $provinces = Region::where('type', 'province')->orderBy('name')->get();

        return Inertia::render('Regions/Index', [
            'regions' => $regions,
            'provinces' => $provinces,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:province,city',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:regions,id|required_if:type,city',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $region = Region::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'parent_id' => $validated['type'] === 'city' ? $validated['parent_id'] : null,
        ]);

        if ($validated['type'] === 'city') {
            $region->shippingRate()->create([
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Wilayah berhasil ditambahkan!');
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'type' => 'required|in:province,city',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:regions,id|required_if:type,city',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $region->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'parent_id' => $validated['type'] === 'city' ? $validated['parent_id'] : null,
        ]);

        if ($validated['type'] === 'city') {
            $region->shippingRate()->updateOrCreate(
                ['region_id' => $region->id],
                ['shipping_cost' => $validated['shipping_cost'] ?? 0]
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Ongkos kirim disimpan.']);
        }

        return redirect()->back()->with('success', 'Wilayah berhasil diperbarui!');
    }

    public function destroy(Region $region)
    {
        if ($region->children()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus wilayah yang memiliki sub-wilayah.']);
        }
        
        $region->delete();

        return redirect()->back()->with('success', 'Wilayah berhasil dihapus!');
    }

    public function bulkUpdateShipping(Request $request)
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:regions,id',
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        $cities = Region::where('parent_id', $validated['province_id'])->where('type', 'city')->get();
        
        foreach ($cities as $city) {
            $city->shippingRate()->updateOrCreate(
                ['region_id' => $city->id],
                ['shipping_cost' => $validated['shipping_cost']]
            );
        }

        return redirect()->back()->with('success', 'Ongkos kirim massal berhasil diterapkan ke seluruh kota di provinsi tersebut!');
    }
}
