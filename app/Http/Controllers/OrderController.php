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
        $paymentMethods = PaymentMethod::active()->ordered()->get(['name', 'code', 'type', 'account_number', 'account_name', 'icon']);
        $provinces = \App\Models\Region::where('type', 'province')->get(['id', 'name']);

        return Inertia::render('CheckoutPage', [
            'paymentMethods' => $paymentMethods,
            'provinces'      => $provinces,
            'isGuest'        => ! auth()->check(),
            'isPartner'      => auth()->check() && auth()->user()->hasRole('reseller'),
            'user'           => auth()->user() ? [
                'name'        => auth()->user()->name,
                'email'       => auth()->user()->email,
                'phone'       => auth()->user()->contact?->phone ?? auth()->user()->phone,
                'address'     => auth()->user()->contact?->address ?? '',
                'province_id' => auth()->user()->contact?->province_id ?? '',
                'city_id'     => auth()->user()->contact?->city_id ?? '',
            ] : null,
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
    public function store(Request $request, \App\Services\ProductPriceService $priceService)
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
                if (!isset($item['id']) || !isset($item['qty'])) {
                    return back()->withErrors(['items' => 'Data item tidak lengkap.']);
                }
            }

            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => array_filter([
                    'required',
                    'email',
                    'max:255',
                    // Only enforce uniqueness when explicitly creating a new account
                    $request->boolean('create_account')
                        ? \Illuminate\Validation\Rule::unique('users', 'email')
                        : (auth()->check()
                            ? \Illuminate\Validation\Rule::unique('users', 'email')->ignore(auth()->id())
                            : null),
                ]),
                'customer_address' => 'required|string|max:1000',
                'province_id' => 'required|exists:regions,id',
                'city_id' => 'required|exists:regions,id',
                'notes' => 'nullable|string|max:1000',
                'payment_method' => 'required|string|exists:payment_methods,code',
                'order_method' => 'required|in:whatsapp,form',
                'whatsapp_url' => 'nullable|url',
                'create_account' => 'nullable|boolean',
                'password' => 'required_if:create_account,1|nullable|min:8|confirmed',
                'payment_mode'        => 'nullable|in:full,dp',
                'down_payment_amount' => 'nullable|numeric|min:0',
            ], [
                'customer_email.unique' => 'Email ini sudah terdaftar sebagai akun. Silakan login terlebih dahulu atau gunakan email lain.',
            ]);

            // Server-side Recalculation to prevent Price Manipulation
            $city = \App\Models\Region::with('shippingRate')->find($validated['city_id']);
            $user = auth()->user();
            $calculatedSubtotal = 0;
            $calculatedItems = [];

            foreach ($items as $item) {
                $product = \App\Models\Product::find($item['id']);
                if (!$product) {
                    return back()->withErrors(['items' => "Produk tidak ditemukan."])->withInput();
                }
                
                $priceData = $priceService->getProductPrice($product, $user, $city);
                $unitPrice = (float) $priceData['price'];
                $qty = (int) $item['qty'];
                $variantLabel = $item['variantLabel'] ?? null;

                if ($qty <= 0) {
                    return back()->withErrors(['items' => "Jumlah kuantitas tidak valid."])->withInput();
                }

                if ($variantLabel && is_array($product->variants)) {
                    foreach ($product->variants as $variant) {
                        if (($variant['label'] ?? '') === $variantLabel) {
                            $unitPrice += (float) ($variant['price_adj'] ?? 0);
                            break;
                        }
                    }
                }

                $itemSubtotal = $unitPrice * $qty;
                $calculatedSubtotal += $itemSubtotal;

                $calculatedItems[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'qty' => $qty,
                    'price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'has_discount' => $priceData['has_discount'],
                    'image' => $product->image_path ? (str_starts_with($product->image_path, 'http') || str_starts_with($product->image_path, '/') ? $product->image_path : '/storage/' . $product->image_path) : null,
                    'variantLabel' => $variantLabel,
                ];
            }

            $calculatedShippingCost = $city && $city->shippingRate ? (float) $city->shippingRate->shipping_cost : 0.0;
            $calculatedTotal = $calculatedSubtotal + $calculatedShippingCost;

            // Enforce calculations onto validated data
            $validated['subtotal'] = $calculatedSubtotal;
            $validated['shipping_cost'] = $calculatedShippingCost;
            $validated['total'] = $calculatedTotal;
            $validated['items'] = $calculatedItems;

            $paymentMode = $request->input('payment_mode', 'full');
            $downPayment = 0;
            $remaining   = 0;
            $paymentStatus = 'unpaid';

            if ($paymentMode === 'dp' && auth()->user()?->hasRole('reseller')) {
                $total = (float) $calculatedTotal;
                $dp    = (float) ($validated['down_payment_amount'] ?? 0);
                if ($dp < 0) {
                    return back()->withErrors(['down_payment_amount' => 'DP minimal Rp 0.'])->withInput();
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
                $customerRole = Role::where('name', 'customer')->first();
                
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
                    'province_id' => $validated['province_id'],
                    'city_id' => $validated['city_id'],
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
                'province_id' => $validated['province_id'],
                'city_id' => $validated['city_id'],
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
