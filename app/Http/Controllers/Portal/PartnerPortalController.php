<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PartnerPortalController extends Controller
{
    /**
     * Display partner dashboard.
     */
    public function dashboard(): InertiaResponse
    {
        $user = Auth::user();
        
        // Get partner's recent orders
        $recentOrders = Sale::where('customer_id', $user->id)
            ->orderByDesc('date')
            ->limit(10)
            ->get();
        
        // Get order statistics
        $totalOrders = Sale::where('customer_id', $user->id)->count();
        $pendingOrders = Sale::where('customer_id', $user->id)
            ->where('shipping_status', 'pending')
            ->count();
        
        // Get billing summary
        $totalBilling = Sale::where('customer_id', $user->id)->sum('grand_total');
        $paidAmount = Payment::where('payable_type', Sale::class)
            ->whereHas('payable', function ($query) {
                $query->where('customer_id', Auth::id());
            })
            ->sum('amount');
        $outstandingBalance = $totalBilling - $paidAmount;
        
        return Inertia::render('Portal/Partner/Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'totalBilling' => $totalBilling,
            'paidAmount' => $paidAmount,
            'outstandingBalance' => $outstandingBalance,
        ]);
    }

    /**
     * Display partner's order history.
     */
    public function orders(Request $request): InertiaResponse
    {
        $query = Sale::where('customer_id', Auth::id())
            ->with('items.product');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('shipping_status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $orders = $query->orderByDesc('date')->paginate(15)->withQueryString();
        
        return Inertia::render('Portal/Partner/Orders', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display partner's billing statement.
     */
    public function billing(Request $request): InertiaResponse
    {
        $query = Sale::where('customer_id', Auth::id())
            ->with(['payments', 'items.product'])
            ->orderByDesc('date');
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $invoices = $query->paginate(15)->withQueryString();
        
        // Calculate totals
        $totalBilling = $invoices->sum('grand_total');
        $totalPaid = Payment::where('payable_type', Sale::class)
            ->whereHas('payable', function ($query) {
                $query->where('customer_id', Auth::id());
            })
            ->sum('amount');
        
        return Inertia::render('Portal/Partner/Billing', [
            'invoices' => $invoices,
            'totalBilling' => $totalBilling,
            'totalPaid' => $totalPaid,
            'filters' => $request->only(['payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display partner profile.
     */
    public function profile(): InertiaResponse
    {
        $user = Auth::user();
        $contact = $user->contact;
        
        return Inertia::render('Portal/Partner/Profile', [
            'user' => $user,
            'contact' => $contact,
        ]);
    }

    /**
     * Update partner profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $request->name,
        ]);

        // Update contact if exists
        if ($user->contact) {
            $user->contact->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
            ]);
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }
}
