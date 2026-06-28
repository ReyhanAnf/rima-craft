<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminPaymentMethodController extends Controller
{
    public function index(): InertiaResponse
    {
        $paymentMethods = PaymentMethod::ordered()->get();
        return Inertia::render('PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:bank,ewallet,qris,cod',
            'code'           => 'required|string|max:50|unique:payment_methods,code',
            'account_number' => 'nullable|string|max:50',
            'account_name'   => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'is_active'      => 'required|boolean',
            'sort_order'     => 'required|integer',
            'logo'           => 'nullable|image|max:2048',
        ]);

        $paymentMethod = new PaymentMethod();
        $paymentMethod->name = $validated['name'];
        $paymentMethod->type = $validated['type'];
        $paymentMethod->code = $validated['code'];
        $paymentMethod->account_number = $validated['account_number'];
        $paymentMethod->account_name = $validated['account_name'];
        $paymentMethod->description = $validated['description'];
        $paymentMethod->is_active = (bool) $validated['is_active'];
        $paymentMethod->sort_order = (int) $validated['sort_order'];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('payment_methods', 'public');
            $paymentMethod->icon = $path;
        }

        $paymentMethod->save();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan!');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:bank,ewallet,qris,cod',
            'code'           => 'required|string|max:50|unique:payment_methods,code,' . $paymentMethod->id,
            'account_number' => 'nullable|string|max:50',
            'account_name'   => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'is_active'      => 'required|boolean',
            'sort_order'     => 'required|integer',
            'logo'           => 'nullable|image|max:2048',
        ]);

        $paymentMethod->name = $validated['name'];
        $paymentMethod->type = $validated['type'];
        $paymentMethod->code = $validated['code'];
        $paymentMethod->account_number = $validated['account_number'];
        $paymentMethod->account_name = $validated['account_name'];
        $paymentMethod->description = $validated['description'];
        $paymentMethod->is_active = (bool) $validated['is_active'];
        $paymentMethod->sort_order = (int) $validated['sort_order'];

        if ($request->hasFile('logo')) {
            if ($paymentMethod->icon) {
                Storage::disk('public')->delete($paymentMethod->icon);
            }
            $path = $request->file('logo')->store('payment_methods', 'public');
            $paymentMethod->icon = $path;
        }

        $paymentMethod->save();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil diperbarui!');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->icon) {
            Storage::disk('public')->delete($paymentMethod->icon);
        }
        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil dihapus!');
    }
}
