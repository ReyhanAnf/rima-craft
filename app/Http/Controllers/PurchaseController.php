<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordPurchaseAction;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Contact;
use App\Models\Material;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PurchaseController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Purchase::with('supplier');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by minimum amount
        if ($request->filled('min_amount')) {
            $query->where('grand_total', '>=', (float) $request->min_amount);
        }

        // Filter by maximum amount
        if ($request->filled('max_amount')) {
            $query->where('grand_total', '<=', (float) $request->max_amount);
        }

        // Search by supplier name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search): void {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $purchases = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => $request->only([
                'search', 'date_from', 'date_to', 'payment_status', 'min_amount', 'max_amount'
            ]),
        ]);
    }

    public function create(): InertiaResponse
    {
        $suppliers = Contact::where('type', 'supplier')->orderBy('name')->get();
        $materials = Material::orderBy('name')->get();

        return Inertia::render('Purchases/Form', [
            'suppliers' => $suppliers,
            'materials' => $materials,
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        try {
            (new RecordPurchaseAction)->handle($request->validated());

            return redirect()->route('purchases.index')
                             ->with('success', 'Transaksi pembelian berhasil disimpan, stok bahan bertambah!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    public function show(Purchase $purchase): InertiaResponse
    {
        $purchase->load(['items.material', 'supplier']);
        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }
}
