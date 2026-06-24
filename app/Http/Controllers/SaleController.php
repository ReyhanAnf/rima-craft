<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordSaleAction;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleStatusRequest;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $query = Sale::with('customer');

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

        // Filter by shipping status
        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $request->shipping_status);
        }

        // Filter by minimum amount
        if ($request->filled('min_amount')) {
            $query->where('grand_total', '>=', (float) $request->min_amount);
        }

        // Filter by maximum amount
        if ($request->filled('max_amount')) {
            $query->where('grand_total', '<=', (float) $request->max_amount);
        }

        // Search by customer name or invoice
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search): void {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->orderByDesc('date')->orderByDesc('id')->paginate(15);

        if ($request->header('HX-Target') === 'sales-list') {
            return view('sales.sales-list', compact('sales'));
        }

        return view('sales.sales-index', compact('sales'));
    }

    public function create(): View
    {
        $customers = Contact::where('type', 'customer')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('sales.sales-form', compact('customers', 'products'));
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            (new RecordSaleAction)->handle($request->validated());

            return redirect()->route('sales.index')
                             ->with('toast', ['message' => 'Transaksi penjualan berhasil disimpan dan stok berkurang!', 'type' => 'success']);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['items.product', 'customer']);
        return view('sales.sales-show', compact('sale'));
    }

    public function updateStatus(UpdateSaleStatusRequest $request, Sale $sale)
    {
        $validated = $request->validated();

        if (isset($validated['shipping_status'])) {
            $sale->update(['shipping_status' => $validated['shipping_status']]);
        }
        if (isset($validated['payment_status'])) {
            $sale->update(['payment_status' => $validated['payment_status']]);
        }

        $sale->load(['items.product', 'customer']);
        return response()
            ->view('sales.sales-show', compact('sale'))
            ->header('HX-Trigger', json_encode(['toast' => ['message' => 'Status berhasil diubah!', 'type' => 'success']]));
    }

    public function printInvoice(Sale $sale): View
    {
        $sale->load(['items.product', 'customer', 'payments.account']);
        return view('sales.print', compact('sale'));
    }
}
