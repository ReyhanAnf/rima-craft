<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordPaymentAction;
use App\Http\Requests\Payment\StorePaymentRequest;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request)
    {
        try {
            (new RecordPaymentAction)->handle($request->validated());

            return back()->with('toast', ['message' => 'Pembayaran cicilan berhasil dicatat!', 'type' => 'success']);
        } catch (\Exception $e) {
            return back()->with('toast', ['message' => 'Gagal: ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
