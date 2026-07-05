<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MyOrderController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MyOrders/Index', ['orders' => $orders]);
    }

    public function show(string $orderNumber): InertiaResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return Inertia::render('MyOrders/Show', ['order' => $order]);
    }

    public function complete(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Only allow completing if order is shipped
        if ($order->status === 'shipped') {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            return redirect()->back()->with('success', 'Pesanan berhasil diselesaikan. Terima kasih!');
        }

        return redirect()->back()->with('error', 'Status pesanan tidak valid untuk diselesaikan.');
    }
}
