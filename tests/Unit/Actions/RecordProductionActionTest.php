<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\RecordProductionAction;
use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Material;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionMaterial;
use App\Models\ProductionResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RecordProductionActionTest extends TestCase
{
    use CreatesTestData;

    private RecordProductionAction $action;
    private Account $account;
    private Material $materialA;
    private Material $materialB;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action    = new RecordProductionAction();
        $this->account   = Account::factory()->create(['balance' => 5_000_000]);
        $this->materialA = Material::factory()->create([
            'current_stock'  => 50,
            'last_buy_price' => 5_000,
        ]);
        $this->materialB = Material::factory()->create([
            'current_stock'  => 50,
            'last_buy_price' => 5_000,
        ]);
        $this->product = Product::factory()->create(['current_stock' => 0]);
    }

    // ---------------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------------

    /**
     * Return minimal valid production data, with optional overrides.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validProductionData(array $overrides = []): array
    {
        return array_merge([
            'date'            => now()->format('Y-m-d'),
            'labor_cost'      => 10_000,
            'overhead_cost'   => 5_000,
            'additional_cost' => 0,
            'notes'           => null,
            'materials'       => [
                ['material_id' => $this->materialA->id, 'qty' => 2],
            ],
            'products'        => [
                ['product_id' => $this->product->id, 'qty' => 3],
            ],
        ], $overrides);
    }

    // ---------------------------------------------------------------------------
    // Test 1 — creates production, material, and result records
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_production_material_and_result_records(): void
    {
        // Arrange
        $data = $this->validProductionData();

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertSame(1, Production::count());
        $this->assertSame(1, ProductionMaterial::count());
        $this->assertSame(1, ProductionResult::count());
    }

    // ---------------------------------------------------------------------------
    // Test 2 — material stock is reduced after production
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_reduces_material_stock(): void
    {
        // Arrange
        $qtyUsed = 5;
        $data = $this->validProductionData([
            'materials' => [
                ['material_id' => $this->materialA->id, 'qty' => $qtyUsed],
            ],
        ]);

        // Act
        $this->action->handle($data);

        // Assert — stock should decrease by qty used
        $this->assertSame(50 - $qtyUsed, $this->materialA->fresh()->current_stock);
    }

    // ---------------------------------------------------------------------------
    // Test 3 — product stock increases after production
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_increases_product_stock(): void
    {
        // Arrange
        $qtyProduced = 5;
        $data = $this->validProductionData([
            'products' => [
                ['product_id' => $this->product->id, 'qty' => $qtyProduced],
            ],
        ]);

        // Act
        $this->action->handle($data);

        // Assert — product stock started at 0, should now equal qty produced
        $this->assertSame($qtyProduced, $this->product->fresh()->current_stock);
    }

    // ---------------------------------------------------------------------------
    // Test 4 — grand_total_cost = material_cost + labor + overhead + additional
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_calculates_grand_total_cost_correctly(): void
    {
        // Arrange
        // materialA: qty=4, last_buy_price=5000 → material cost = 20_000
        $laborCost      = 15_000;
        $overheadCost   = 8_000;
        $additionalCost = 2_000;
        $materialQty    = 4;
        $expectedMaterialCost    = $materialQty * 5_000; // 20_000
        $expectedGrandTotalCost  = $expectedMaterialCost + $laborCost + $overheadCost + $additionalCost; // 45_000

        $data = $this->validProductionData([
            'labor_cost'      => $laborCost,
            'overhead_cost'   => $overheadCost,
            'additional_cost' => $additionalCost,
            'materials'       => [
                ['material_id' => $this->materialA->id, 'qty' => $materialQty],
            ],
        ]);

        // Act
        $production = $this->action->handle($data);

        // Assert
        $this->assertEquals($expectedGrandTotalCost, (float) $production->grand_total_cost);
        $this->assertEquals($expectedMaterialCost, (float) $production->total_material_cost);
    }

    // ---------------------------------------------------------------------------
    // Test 5 — creates a cash ledger entry with category production_labor
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_labor_cost_ledger_entry(): void
    {
        // Arrange
        $data = $this->validProductionData(['labor_cost' => 20_000]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertSame(
            1,
            CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_LABOR)->count()
        );
    }

    // ---------------------------------------------------------------------------
    // Test 6 — creates a cash ledger entry with category production_overhead
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_overhead_cost_ledger_entry(): void
    {
        // Arrange
        $data = $this->validProductionData(['overhead_cost' => 10_000]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertSame(
            1,
            CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_OVERHEAD)->count()
        );
    }

    // ---------------------------------------------------------------------------
    // Test 7 — creates a cash ledger entry with category production_material
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_material_cost_ledger_entry(): void
    {
        // Arrange — material_cost = 2 * 5000 = 10_000 (always > 0 so ledger will be created)
        $data = $this->validProductionData([
            'materials' => [
                ['material_id' => $this->materialA->id, 'qty' => 2],
            ],
        ]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertSame(
            1,
            CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_MATERIAL)->count()
        );
    }

    // ---------------------------------------------------------------------------
    // Test 8 — throws exception when material stock is insufficient
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_throws_exception_when_material_stock_insufficient(): void
    {
        // Arrange — request more than available stock (50)
        $data = $this->validProductionData([
            'materials' => [
                ['material_id' => $this->materialA->id, 'qty' => 100],
            ],
        ]);

        // Assert then Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($this->materialA->name, '/') . '/');

        $this->action->handle($data);
    }

    // ---------------------------------------------------------------------------
    // Test 9 (PBT) — SUM(ledger.amount) == grand_total_cost across 50 iterations
    //
    // Property 3: SUM(ledger.amount) == grand_total_cost
    // Validates: Requirements 21.3
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_total_ledger_amount_equals_grand_total_cost(): void
    {
        for ($i = 0; $i < 50; $i++) {
            // Use a fresh account per iteration so ledger sums are isolated
            $account = Account::factory()->create(['balance' => 999_999_999]);

            // Random cost inputs
            $laborCost      = (float) fake()->randomFloat(2, 0, 50_000);
            $overheadCost   = (float) fake()->randomFloat(2, 0, 50_000);
            $additionalCost = (float) fake()->randomFloat(2, 0, 20_000);
            $materialQty    = fake()->numberBetween(1, 10);

            // Create fresh materials for this iteration (last_buy_price=5000, stock=50)
            $material = Material::factory()->create([
                'current_stock'  => 50,
                'last_buy_price' => 5_000,
            ]);
            $product = Product::factory()->create(['current_stock' => 0]);

            $expectedMaterialCost   = $materialQty * 5_000;
            $expectedGrandTotalCost = $expectedMaterialCost + $laborCost + $overheadCost + $additionalCost;

            $data = [
                'date'            => now()->format('Y-m-d'),
                'labor_cost'      => $laborCost,
                'overhead_cost'   => $overheadCost,
                'additional_cost' => $additionalCost,
                'notes'           => null,
                'materials'       => [
                    ['material_id' => $material->id, 'qty' => $materialQty],
                ],
                'products'        => [
                    ['product_id' => $product->id, 'qty' => 1],
                ],
            ];

            $production = $this->action->handle($data);

            // Sum all ledger entries that reference this specific production
            $ledgerSum = (float) CashLedger::where('reference_type', Production::class)
                ->where('reference_id', $production->id)
                ->sum('amount');

            $this->assertEquals(
                round($expectedGrandTotalCost, 2),
                round($ledgerSum, 2),
                "Iteration {$i}: SUM(ledger.amount) [{$ledgerSum}] != grand_total_cost [{$expectedGrandTotalCost}]"
            );
        }
    }
}
