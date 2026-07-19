<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordSaleAction;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleStatusRequest;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    public function index(Request $request): InertiaResponse
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

        $sales = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only([
                'search', 'date_from', 'date_to', 'payment_status', 'shipping_status', 'min_amount', 'max_amount'
            ]),
        ]);
    }

    public function create(): InertiaResponse
    {
        $customers = Contact::where('type', 'customer')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Sales/Form', [
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            (new RecordSaleAction)->handle($request->validated());

            return redirect()->route('sales.index')
                             ->with('success', 'Transaksi penjualan berhasil disimpan dan stok berkurang!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Sale $sale): InertiaResponse
    {
        $sale->load(['items.product', 'customer', 'payments.account']);
        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
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

        return redirect()->back()->with('success', 'Status berhasil diubah!');
    }

    public function printInvoice(Sale $sale): View
    {
        $sale->load(['items.product', 'customer', 'payments.account']);
        return view('sales.print', compact('sale'));
    }

    public function downloadPdf(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'payments.account']);
        $pdf = Pdf::loadView('sales.pdf', [
            'sale' => $sale,
        ]);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . ($sale->invoice_number ? str_replace('/', '-', $sale->invoice_number) : $sale->id) . '.pdf';

        return $pdf->download($filename);
    }
}
