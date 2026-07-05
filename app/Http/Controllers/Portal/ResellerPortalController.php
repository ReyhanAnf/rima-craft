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

class ResellerPortalController extends Controller
{
    /**
     * Display reseller dashboard.
     */
    public function dashboard(): InertiaResponse
    {
        $user = Auth::user();
        
        // Get reseller's recent orders
        $recentOrders = \App\Models\Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        
        // Get order statistics
        $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
        $pendingOrders = \App\Models\Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // Get billing summary
        $totalBilling = (float) \App\Models\Order::where('user_id', $user->id)->sum('total');
        $outstandingBalance = (float) \App\Models\Order::where('user_id', $user->id)->sum('remaining_balance');
        $paidAmount = $totalBilling - $outstandingBalance;
        
        return Inertia::render('Portal/Reseller/Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'totalBilling' => $totalBilling,
            'paidAmount' => $paidAmount,
            'outstandingBalance' => $outstandingBalance,
        ]);
    }

    /**
     * Display reseller's order history.
     */
    public function orders(Request $request): InertiaResponse
    {
        $query = \App\Models\Order::where('user_id', Auth::id());
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        
        return Inertia::render('Portal/Reseller/Orders', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display reseller's billing statement.
     */
    public function billing(Request $request): InertiaResponse
    {
        $query = \App\Models\Order::where('user_id', Auth::id())
            ->with(['payments'])
            ->orderByDesc('created_at');
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $invoices = $query->paginate(15)->withQueryString();
        
        // Calculate totals
        $totalBilling = (float) \App\Models\Order::where('user_id', Auth::id())->sum('total');
        $outstandingBalance = (float) \App\Models\Order::where('user_id', Auth::id())->sum('remaining_balance');
        $totalPaid = $totalBilling - $outstandingBalance;
        
        return Inertia::render('Portal/Reseller/Billing', [
            'invoices' => $invoices,
            'totalBilling' => $totalBilling,
            'totalPaid' => $totalPaid,
            'filters' => $request->only(['payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display reseller profile.
     */
    public function profile(): InertiaResponse
    {
        $user = Auth::user();
        $contact = $user->contact;
        $provinces = \App\Models\Region::where('type', 'province')->orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Portal/Reseller/Profile', [
            'user' => $user,
            'contact' => $contact,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Update reseller profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:regions,id',
            'current_password' => 'nullable|string|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        
        $userUpdate = [
            'name' => $request->name,
        ];

        if ($request->filled('new_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'current_password' => ['Password saat ini tidak cocok.'],
                ]);
            }
            $userUpdate['password'] = \Illuminate\Support\Facades\Hash::make($request->new_password);
        }

        $user->update($userUpdate);

        // Update contact if exists
        if ($user->contact) {
            $user->contact->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
            ]);
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }
}
