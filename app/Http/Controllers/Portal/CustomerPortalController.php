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
        $recentOrders = \App\Models\Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        // Get order statistics
        $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
        $pendingOrders = \App\Models\Order::where('user_id', $user->id)
            ->where('status', 'pending')
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
        $query = \App\Models\Order::where('user_id', Auth::id());
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        
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
        $provinces = \App\Models\Region::where('type', 'province')->orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Portal/Customer/Profile', [
            'user' => $user,
            'contact' => $contact,
            'provinces' => $provinces,
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
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
            ]);
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }
}
