<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\Sale\StoreSaleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for StoreSaleRequest validation rules.
 *
 * Uses Validator::make($data, $request->rules()) — no HTTP request needed.
 * The "passes" test inserts a minimal product row (rolled back via DatabaseTransactions).
 *
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4
 */
class StoreSaleRequestTest extends TestCase
{
    /** Insert a minimal product row and return its id. */
    private function createProduct(): int
    {
        return (int) DB::table('products')->insertGetId([
            'name'          => 'Test Product',
            'base_price'    => 50000,
            'current_stock' => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /** Return a complete, valid payload for StoreSaleRequest. */
    private function validData(int $productId = 1): array
    {
        return [
            'date'             => '2026-01-15',
            'payment_status'   => 'unpaid',
            'shipping_status'  => 'pending',
            'items'            => [
                [
                    'product_id' => $productId,
                    'qty'        => 2,
                    'price'      => 50000,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Requirement 11.1 — passes with valid data
    // -------------------------------------------------------------------------

    #[Test]
    public function passes_with_valid_data(): void
    {
        $productId = $this->createProduct();
        $request   = new StoreSaleRequest();
        $validator = Validator::make($this->validData($productId), $request->rules());

        $this->assertTrue(
            $validator->passes(),
            'Validator should pass with complete, valid data. Errors: '
                . implode(', ', $validator->errors()->all())
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 11.2 — fails without date or items
    // -------------------------------------------------------------------------

    #[Test]
    public function fails_without_date_or_items(): void
    {
        $request = new StoreSaleRequest();

        // Missing 'date'
        $dataMissingDate = $this->validData();
        unset($dataMissingDate['date']);

        $validatorMissingDate = Validator::make($dataMissingDate, $request->rules());
        $this->assertTrue(
            $validatorMissingDate->fails(),
            'Validator should fail when "date" is missing.'
        );
        $this->assertArrayHasKey('date', $validatorMissingDate->errors()->toArray());

        // Missing 'items'
        $dataMissingItems = $this->validData();
        unset($dataMissingItems['items']);

        $validatorMissingItems = Validator::make($dataMissingItems, $request->rules());
        $this->assertTrue(
            $validatorMissingItems->fails(),
            'Validator should fail when "items" is missing.'
        );
        $this->assertArrayHasKey('items', $validatorMissingItems->errors()->toArray());

        // 'items' as empty array
        $dataEmptyItems          = $this->validData();
        $dataEmptyItems['items'] = [];

        $validatorEmptyItems = Validator::make($dataEmptyItems, $request->rules());
        $this->assertTrue(
            $validatorEmptyItems->fails(),
            'Validator should fail when "items" is an empty array.'
        );
        $this->assertArrayHasKey('items', $validatorEmptyItems->errors()->toArray());
    }

    // -------------------------------------------------------------------------
    // Requirement 11.3 — fails when qty is zero or negative
    // -------------------------------------------------------------------------

    #[Test]
    public function fails_when_qty_is_zero_or_negative(): void
    {
        $request = new StoreSaleRequest();

        // qty = 0
        $dataQtyZero                        = $this->validData();
        $dataQtyZero['items'][0]['qty']     = 0;

        $validatorQtyZero = Validator::make($dataQtyZero, $request->rules());
        $this->assertTrue(
            $validatorQtyZero->fails(),
            'Validator should fail when items[0].qty = 0.'
        );
        $this->assertArrayHasKey('items.0.qty', $validatorQtyZero->errors()->toArray());

        // qty = -1 (negative)
        $dataQtyNeg                         = $this->validData();
        $dataQtyNeg['items'][0]['qty']      = -1;

        $validatorQtyNeg = Validator::make($dataQtyNeg, $request->rules());
        $this->assertTrue(
            $validatorQtyNeg->fails(),
            'Validator should fail when items[0].qty is negative.'
        );
        $this->assertArrayHasKey('items.0.qty', $validatorQtyNeg->errors()->toArray());
    }

    // -------------------------------------------------------------------------
    // Requirement 11.4 — fails with invalid payment_status
    // -------------------------------------------------------------------------

    #[Test]
    public function fails_with_invalid_payment_status(): void
    {
        $request = new StoreSaleRequest();

        foreach (['pending', 'done', 'cancelled', '', 'PAID', 'Unpaid'] as $invalidStatus) {
            $data                    = $this->validData();
            $data['payment_status']  = $invalidStatus;

            $validator = Validator::make($data, $request->rules());
            $this->assertTrue(
                $validator->fails(),
                "Validator should fail for payment_status = '{$invalidStatus}'."
            );
            $this->assertArrayHasKey(
                'payment_status',
                $validator->errors()->toArray(),
                "Expected 'payment_status' error for value '{$invalidStatus}'."
            );
        }
    }
}
