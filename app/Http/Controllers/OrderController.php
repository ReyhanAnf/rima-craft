<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Role;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    /**
     * Display checkout page via Inertia (Vue)
     */
    public function checkout(): InertiaResponse
    {
        $paymentMethods = PaymentMethod::active()->ordered()->get(['name', 'code', 'type', 'account_number', 'account_name']);

        return Inertia::render('CheckoutPage', [
            'paymentMethods' => $paymentMethods,
            'isGuest'        => ! auth()->check(),
            'isPartner'      => auth()->check() && auth()->user()->hasRole('reseller'),
            'user'           => auth()->user()?->only(['name', 'email', 'phone']),
            'config'         => [
                'business_name'    => config('settings.business_name', 'Rima Craft'),
                'business_phone'   => config('settings.business_phone', '6281234567890'),
                'hero_description' => config('settings.hero_description', ''),
                'checkout_url'     => route('order.checkout'),
                'order_store_url'  => route('order.store'),
                'catalog_url'      => route('catalog.index'),
                'login_url'        => route('login'),
                'terms_url'        => route('page.terms'),
                'privacy_url'      => route('page.privacy'),
                'shipping_url'     => route('page.shipping'),
            ],
        ]);
    }

    /**
     * @deprecated Legacy create — kept for backward compatibility
     */
    public function create()
    {
        return redirect()->route('order.checkout');
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
                'payment_mode'        => 'nullable|in:full,dp',
                'down_payment_amount' => 'nullable|numeric|min:0',
            ]);

            // Add items to validated data
            $validated['items'] = $items;

            $paymentMode = $request->input('payment_mode', 'full');
            $downPayment = 0;
            $remaining   = 0;
            $paymentStatus = 'unpaid';

            if ($paymentMode === 'dp' && auth()->user()?->hasRole('reseller')) {
                $total = (float) ($validated['total'] ?? 0);
                $dp    = (float) ($validated['down_payment_amount'] ?? 0);
                if ($dp < $total * 0.3) {
                    return back()->withErrors(['down_payment_amount' => 'DP minimal 30% dari total order.'])->withInput();
                }
                $downPayment   = min($dp, $total);
                $remaining     = max(0.0, $total - $downPayment);
                $paymentStatus = $remaining > 0 ? 'partial' : 'unpaid';
            }

            DB::beginTransaction();

            // Create user account if requested
            $userId = auth()->id();
            if ($request->input('create_account') && !$userId) {
                // Find or create customer role
                $customerRole = Role::where('slug', 'customer')->first();
                
                $user = User::create([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'customer',
                ]);

                // Assign customer role
                if ($customerRole) {
                    $user->roles()->attach($customerRole->id);
                }

                // Create contact for the user
                Contact::create([
                    'user_id' => $user->id,
                    'type' => 'customer',
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'phone' => $validated['customer_phone'],
                    'address' => $validated['customer_address'],
                ]);

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
                'payment_status'      => $paymentStatus,
                'down_payment_amount' => $downPayment,
                'remaining_balance'   => $remaining,
            ]);

            DB::commit();

            Log::info('New order created', [
                'order_number' => $order->order_number,
                'customer'     => $order->customer_name,
                'total'        => $order->total,
                'user_created' => (bool) $request->input('create_account'),
            ]);

            return redirect()->route('order.success', ['order' => $order->order_number]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat membuat pesanan.'])->withInput();
        }
    }

    /**
     * Display order success page via Inertia (Vue)
     */
    public function success(string $orderNumber): InertiaResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $paymentMethodDetail = PaymentMethod::where('code', $order->payment_method)
            ->first(['name', 'account_number', 'account_name', 'description']);

        return Inertia::render('OrderSuccessPage', [
            'order' => array_merge($order->toArray(), [
                'payment_method_detail' => $paymentMethodDetail,
                'created_at_formatted'  => $order->created_at->format('d M Y, H:i'),
            ]),
        ]);
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
