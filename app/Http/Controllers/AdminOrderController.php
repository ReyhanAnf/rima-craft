<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = Order::with('user');

        // Filter by search (order number, customer name, email, phone)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): InertiaResponse
    {
        $order->load(['user']);
        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,confirmed,processing,shipped,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'cancellation_reason' => 'nullable|string|max:1000',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        $toastMessage = 'Order berhasil diperbarui!';

        // Update payment proof if uploaded
        if ($request->hasFile('payment_proof')) {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $order->update(['payment_proof' => $path]);
            $toastMessage = 'Bukti pembayaran berhasil diunggah!';
        }

        // Update payment status
        if ($request->filled('payment_status')) {
            $oldPaymentStatus = $order->payment_status;
            $newPaymentStatus = $validated['payment_status'];
            if ($oldPaymentStatus !== $newPaymentStatus) {
                $order->update(['payment_status' => $newPaymentStatus]);
                $toastMessage = 'Status pembayaran berhasil diubah!';
            }
        }

        // Update order status
        if ($request->filled('status')) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            if ($oldStatus !== $newStatus) {
                if ($newStatus === 'confirmed') {
                    $order->confirm();
                } elseif ($newStatus === 'processing') {
                    $order->markProcessing();
                } elseif ($newStatus === 'shipped') {
                    $order->markShipped();
                } elseif ($newStatus === 'completed') {
                    $order->complete();
                } elseif ($newStatus === 'cancelled') {
                    $order->cancel($validated['cancellation_reason'] ?? '');
                } else {
                    $order->update(['status' => 'pending']);
                }
                $toastMessage = 'Status pesanan berhasil diubah!';
            }
        }

        return redirect()->back()->with('success', $toastMessage);
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dihapus!');
    }
}
