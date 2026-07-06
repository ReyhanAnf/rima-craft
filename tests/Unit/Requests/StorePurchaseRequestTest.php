<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Material;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for StorePurchaseRequest validation rules.
 *
 * Uses Validator::make($data, $request->rules()) — no HTTP request needed.
 * The "passes" test creates a Material via factory (rolled back via DatabaseTransactions).
 *
 * Validates: Requirements 11.5, 11.6
 */
class StorePurchaseRequestTest extends TestCase
{
    /** Return a complete, valid payload for StorePurchaseRequest. */
    private function validData(int $materialId): array
    {
        return [
            'date'           => '2026-01-15',
            'supplier_id'    => null,
            'payment_status' => 'unpaid',
            'items'          => [
                [
                    'material_id' => $materialId,
                    'qty'         => 2,
                    'price'       => 15000,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Requirement 11.5 — passes with valid data
    // -------------------------------------------------------------------------

    #[Test]
    public function it_passes_with_valid_data(): void
    {
        $material  = Material::factory()->create();
        $request   = new StorePurchaseRequest();
        $validator = Validator::make($this->validData($material->id), $request->rules());

        $this->assertTrue(
            $validator->passes(),
            'Validator should pass with complete, valid data. Errors: '
                . implode(', ', $validator->errors()->all())
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 11.6 — fails when material_id does not exist in DB
    // -------------------------------------------------------------------------

    #[Test]
    public function it_fails_when_material_id_does_not_exist(): void
    {
        // Use an ID that is extremely unlikely to exist
        $nonExistentId = 999999;
        $request       = new StorePurchaseRequest();
        $validator     = Validator::make($this->validData($nonExistentId), $request->rules());

        $this->assertTrue(
            $validator->fails(),
            'Validator should fail when items[0].material_id does not exist in DB.'
        );
        $this->assertArrayHasKey(
            'items.0.material_id',
            $validator->errors()->toArray(),
            "Expected validation error on 'items.0.material_id'."
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 11.5, 11.6 — fails without required fields
    // -------------------------------------------------------------------------

    #[Test]
    public function it_fails_without_required_fields(): void
    {
        $request = new StorePurchaseRequest();

        // Missing 'date'
        $dataMissingDate = $this->validData(1);
        unset($dataMissingDate['date']);

        $validatorMissingDate = Validator::make($dataMissingDate, $request->rules());
        $this->assertTrue(
            $validatorMissingDate->fails(),
            'Validator should fail when "date" is missing.'
        );
        $this->assertArrayHasKey('date', $validatorMissingDate->errors()->toArray());

        // Missing 'items'
        $dataMissingItems = $this->validData(1);
        unset($dataMissingItems['items']);

        $validatorMissingItems = Validator::make($dataMissingItems, $request->rules());
        $this->assertTrue(
            $validatorMissingItems->fails(),
            'Validator should fail when "items" is missing.'
        );
        $this->assertArrayHasKey('items', $validatorMissingItems->errors()->toArray());
    }
}
