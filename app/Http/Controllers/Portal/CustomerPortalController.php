<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CustomerPortalController extends Controller
{
    /**
     * Display customer dashboard.
     */
    public function dashboard(): InertiaResponse
    {
        $user = Auth::user();
        
        // Get customer's recent orders
        $recentOrders = Sale::where('customer_id', $user->id)
            ->orderByDesc('date')
            ->limit(5)
            ->get();
        
        // Get order statistics
        $totalOrders = Sale::where('customer_id', $user->id)->count();
        $pendingOrders = Sale::where('customer_id', $user->id)
            ->where('shipping_status', 'pending')
            ->count();
        
        return Inertia::render('Portal/Customer/Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
        ]);
    }

    /**
     * Display customer's order history.
     */
    public function orders(Request $request): InertiaResponse
    {
        $query = Sale::where('customer_id', Auth::id())
            ->with('items.product');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('shipping_status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $orders = $query->orderByDesc('date')->paginate(15)->withQueryString();
        
        return Inertia::render('Portal/Customer/Orders', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display customer profile.
     */
    public function profile(): InertiaResponse
    {
        $user = Auth::user();
        $contact = $user->contact;
        
        return Inertia::render('Portal/Customer/Profile', [
            'user' => $user,
            'contact' => $contact,
        ]);
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
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
            ]);
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }
}
