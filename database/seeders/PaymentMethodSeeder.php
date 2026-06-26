<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            // Bank Transfer
            [
                'name' => 'Transfer BCA',
                'type' => 'bank',
                'code' => 'bca',
                'account_number' => '1234567890',
                'account_name' => 'PT Rima Craft Indonesia',
                'description' => 'Transfer ke rekening BCA',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Transfer Mandiri',
                'type' => 'bank',
                'code' => 'mandiri',
                'account_number' => '0987654321',
                'account_name' => 'PT Rima Craft Indonesia',
                'description' => 'Transfer ke rekening Mandiri',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Transfer BRI',
                'type' => 'bank',
                'code' => 'bri',
                'account_number' => '5678901234',
                'account_name' => 'PT Rima Craft Indonesia',
                'description' => 'Transfer ke rekening BRI',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Transfer BNI',
                'type' => 'bank',
                'code' => 'bni',
                'account_number' => '3456789012',
                'account_name' => 'PT Rima Craft Indonesia',
                'description' => 'Transfer ke rekening BNI',
                'is_active' => false,
                'sort_order' => 4,
            ],

            // E-Wallet
            [
                'name' => 'GoPay',
                'type' => 'ewallet',
                'code' => 'gopay',
                'account_number' => '081234567890',
                'account_name' => 'Rima Craft',
                'description' => 'Bayar via GoPay',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'OVO',
                'type' => 'ewallet',
                'code' => 'ovo',
                'account_number' => '081234567890',
                'account_name' => 'Rima Craft',
                'description' => 'Bayar via OVO',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'DANA',
                'type' => 'ewallet',
                'code' => 'dana',
                'account_number' => '081234567890',
                'account_name' => 'Rima Craft',
                'description' => 'Bayar via DANA',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'code' => 'shopeepay',
                'account_number' => '081234567890',
                'account_name' => 'Rima Craft',
                'description' => 'Bayar via ShopeePay',
                'is_active' => false,
                'sort_order' => 8,
            ],

            // Retail / Others
            [
                'name' => 'QRIS',
                'type' => 'qris',
                'code' => 'qris',
                'account_number' => null,
                'account_name' => 'Rima Craft',
                'description' => 'Scan QR untuk pembayaran (all payment)',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Bayar di Tempat (COD)',
                'type' => 'cod',
                'code' => 'cod',
                'account_number' => null,
                'account_name' => null,
                'description' => 'Bayar saat barang diterima',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
