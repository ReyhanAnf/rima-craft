<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\RecordPurchaseAction;
use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Contact;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RecordPurchaseActionTest extends TestCase
{
    use CreatesTestData;

    private RecordPurchaseAction $action;
    private Account $account;
    private Material $materialA;
    private Material $materialB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RecordPurchaseAction();

        // RecordPurchaseAction uses Account::first(), so ensure our account
        // is the first one by truncating and force-creating with id=1.
        Account::query()->forceDelete();
        $this->account = Account::forceCreate([
            'id'      => 1,
            'name'    => 'Kas Utama',
            'type'    => 'cash',
            'balance' => 1_000_000,
        ]);

        $this->materialA = Material::factory()->create(['current_stock' => 10, 'last_buy_price' => 5_000]);
        $this->materialB = Material::factory()->create(['current_stock' => 10, 'last_buy_price' => 3_000]);
    }

    // ---------------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------------

    private function validPurchaseData(array $overrides = []): array
    {
        return array_merge([
            'date'           => now()->format('Y-m-d'),
            'payment_status' => 'unpaid',
            'items'          => [
                ['material_id' => $this->materialA->id, 'qty' => 2, 'price' => 10_000],
            ],
        ], $overrides);
    }

    // ---------------------------------------------------------------------------
    // Test 1: Creates purchase and purchase items
    // Requirements: 3.1
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_purchase_and_purchase_items(): void
    {
        // Arrange
        $data = $this->validPurchaseData([
            'items' => [
                ['material_id' => $this->materialA->id, 'qty' => 2, 'price' => 10_000],
                ['material_id' => $this->materialB->id, 'qty' => 3, 'price' => 6_000],
            ],
        ]);

        // Act
        $purchase = $this->action->handle($data);

        // Assert
        $this->assertInstanceOf(Purchase::class, $purchase);
        $this->assertEquals(1, Purchase::count());
        $this->assertEquals(2, PurchaseItem::count());
        $this->assertEquals(
            (2 * 10_000) + (3 * 6_000),
            (int) $purchase->total_amount
        );
    }

    // ---------------------------------------------------------------------------
    // Test 2: Increases material stock
    // Requirements: 3.2
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_increases_material_stock(): void
    {
        // Arrange
        $qtyToBuy = 5;
        $data = $this->validPurchaseData([
            'items' => [
                ['material_id' => $this->materialA->id, 'qty' => $qtyToBuy, 'price' => 10_000],
            ],
        ]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(10 + $qtyToBuy, $this->materialA->fresh()->current_stock);
    }

    // ---------------------------------------------------------------------------
    // Test 3: Updates last_buy_price when price is positive
    // Requirements: 3.3
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_updates_last_buy_price_when_price_is_positive(): void
    {
        // Arrange
        $newPrice = 15_000;
        $data = $this->validPurchaseData([
            'items' => [
                ['material_id' => $this->materialA->id, 'qty' => 2, 'price' => $newPrice],
            ],
        ]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals($newPrice, (int) $this->materialA->fresh()->last_buy_price);
    }

    // ---------------------------------------------------------------------------
    // Test 4: Creates payment and ledger with type 'out' when paid
    // Requirements: 3.4
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_payment_and_ledger_type_out_when_paid(): void
    {
        // Arrange
        $data = $this->validPurchaseData(['payment_status' => 'paid']);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, CashLedger::where('type', 'out')->count());
    }

    // ---------------------------------------------------------------------------
    // Test 5: Decreases account balance when paid
    // Requirements: 3.5
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_decreases_account_balance_when_paid(): void
    {
        // Arrange
        $qty   = 2;
        $price = 10_000;
        $data  = $this->validPurchaseData([
            'payment_status' => 'paid',
            'items'          => [
                ['material_id' => $this->materialA->id, 'qty' => $qty, 'price' => $price],
            ],
        ]);
        $expectedBalance = 1_000_000 - ($qty * $price);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals($expectedBalance, (int) $this->account->fresh()->balance);
    }

    // ---------------------------------------------------------------------------
    // Test 6: Does not create payment or ledger when unpaid
    // Requirements: 3.6
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_does_not_create_payment_or_ledger_when_unpaid(): void
    {
        // Arrange
        $data = $this->validPurchaseData(['payment_status' => 'unpaid']);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(0, Payment::count());
        $this->assertEquals(0, CashLedger::count());
    }

    // ---------------------------------------------------------------------------
    // Test 7: Creates supplier contact when save_supplier is true
    // Requirements: 3.7
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_supplier_contact_when_save_supplier_is_true(): void
    {
        // Arrange
        $data = $this->validPurchaseData([
            'save_supplier'  => true,
            'supplier_name'  => 'Toko Bahan Pak Budi',
            'supplier_phone' => '08123456789',
        ]);

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(1, Contact::where('type', 'supplier')->count());
        $this->assertDatabaseHas('contacts', [
            'type' => 'supplier',
            'name' => 'Toko Bahan Pak Budi',
        ]);
    }

    // ---------------------------------------------------------------------------
    // PBT (from task 5.2): total_amount = SUM(qty_i × price_i)
    // Property 2: total_amount = SUM(qty_i × price_i)
    // Validates: Requirements 21.2
    // Requirements: 3.8, 21.2
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_total_amount_equals_sum_of_qty_times_price(): void
    {
        for ($i = 0; $i < 50; $i++) {
            // Generate 1-3 random items per iteration
            $itemCount = fake()->numberBetween(1, 3);
            $materials = [
                $this->materialA,
                $this->materialB,
            ];

            $items          = [];
            $expectedTotal  = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $material = $materials[$j % count($materials)];
                $qty      = fake()->numberBetween(1, 10);
                $price    = fake()->numberBetween(1_000, 50_000);

                $items[]       = [
                    'material_id' => $material->id,
                    'qty'         => $qty,
                    'price'       => $price,
                ];
                $expectedTotal += $qty * $price;
            }

            $data = [
                'date'           => now()->format('Y-m-d'),
                'payment_status' => 'unpaid',
                'items'          => $items,
            ];

            $purchase = $this->action->handle($data);

            $this->assertEquals(
                $expectedTotal,
                (int) $purchase->total_amount,
                "Iteration {$i}: total_amount does not equal SUM(qty_i * price_i). " .
                "Expected: {$expectedTotal}, Got: {$purchase->total_amount}"
            );
        }
    }
}
