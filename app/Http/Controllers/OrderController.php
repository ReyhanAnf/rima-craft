<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display order form (public - no login required)
     */
    public function create()
    {
        return view('orders.create');
    }

    /**
     * Store a new order (public endpoint)
     */
    public function store(Request $request)
    {
        try {
            // Decode items from JSON string to array
            $itemsJson = $request->input('items');
            $items = is_string($itemsJson) ? json_decode($itemsJson, true) : $itemsJson;

            // Validate items manually
            if (!$items || !is_array($items) || count($items) === 0) {
                return back()->withErrors(['items' => 'Keranjang belanja kosong.'])->withInput();
            }

            foreach ($items as $index => $item) {
                if (!isset($item['id']) || !isset($item['name']) || !isset($item['qty']) || !isset($item['price'])) {
                    return back()->withErrors(['items' => 'Data item tidak lengkap.']);
                }
            }

            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => [
                    'required',
                    'email',
                    'max:255',
                    $request->input('create_account')
                        ? \Illuminate\Validation\Rule::unique('users', 'email')
                        : (auth()->check() ? \Illuminate\Validation\Rule::unique('users', 'email')->ignore(auth()->id()) : ''),
                ],
                'customer_address' => 'required|string|max:1000',
                'subtotal' => 'required|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'payment_method' => 'required|string|exists:payment_methods,code',
                'order_method' => 'required|in:whatsapp,form',
                'whatsapp_url' => 'nullable|url',
                'create_account' => 'nullable|boolean',
                'password' => 'required_if:create_account,1|nullable|min:8|confirmed',
            ]);

            // Add items to validated data
            $validated['items'] = $items;

            DB::beginTransaction();

            // Create user account if requested
            $userId = auth()->id();
            if ($request->input('create_account') && !$userId) {
                // Find or create customer role
                $customerRole = Role::where('slug', 'customer')->first();
                
                $user = User::create([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'phone' => $validated['customer_phone'],
                    'password' => Hash::make($validated['password']),
                    'status' => 'active',
                ]);

                // Assign customer role
                if ($customerRole) {
                    $user->roles()->attach($customerRole);
                }

                // Auto login the user
                auth()->login($user);
                $userId = $user->id;

                Log::info('New customer account created during checkout', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'items' => $validated['items'],
                'subtotal' => $validated['subtotal'],
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'order_method' => $validated['order_method'],
                'whatsapp_url' => $validated['whatsapp_url'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            DB::commit();

            // Log for debugging
            Log::info('New order created', [
                'order_number' => $order->order_number,
                'customer' => $order->customer_name,
                'total' => $order->total,
                'user_created' => $request->input('create_account') ? true : false,
            ]);

            // HTMX redirect to success page
            if ($request->header('HX-Request')) {
                return response()->make('', 200, [
                    'HX-Redirect' => route('order.success', ['order' => $order->order_number])
                ]);
            }

            $successMessage = 'Pesanan Anda berhasil dibuat!';
            if ($request->input('create_account')) {
                $successMessage .= ' Akun Anda juga telah dibuat. Silakan login untuk melihat riwayat pesanan.';
            }

            return redirect()->route('order.success', ['order' => $order->order_number])
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // HTMX error response with toast
            if ($request->header('HX-Request')) {
                $errors = $e->validator->errors()->first();
                return response($errors, 422)->header('HX-Reswap', 'none');
            }

            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // HTMX error response with toast
            if ($request->header('HX-Request')) {
                return response('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi kami via WhatsApp.', 500)
                    ->header('HX-Reswap', 'none');
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat membuat pesanan.']);
        }
    }

    /**
     * Display order success page
     */
    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('orders.success', compact('order'));
    }

    /**
     * Display customer order history (if logged in)
     */
    public function myOrders()
    {
        $orders = auth()->user()
            ->orders()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.my-orders', compact('orders'));
    }

    /**
     * Display specific order details
     */
    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Check authorization
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('orders.show', compact('order'));
    }
}
