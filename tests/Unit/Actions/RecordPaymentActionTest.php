<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\RecordPaymentAction;
use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RecordPaymentActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private RecordPaymentAction $action;
    private Account $account;
    private Sale $sale;
    private Purchase $purchase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new RecordPaymentAction();

        // RecordPaymentAction hardcodes Account::findOrFail(1), so we must ensure
        // the account has id=1 by truncating and re-inserting with explicit id.
        Account::query()->forceDelete();
        $this->account = Account::forceCreate([
            'id'      => 1,
            'name'    => 'Kas Utama',
            'type'    => 'cash',
            'balance' => 2_000_000,
        ]);

        $this->sale = Sale::factory()->create([
            'grand_total'    => 500_000,
            'payment_status' => 'unpaid',
        ]);

        $this->purchase = Purchase::factory()->create([
            'total_amount'   => 300_000,
            'payment_status' => 'unpaid',
        ]);
    }

    // ---------------------------------------------------------------------------
    // 1. Sale → creates Payment + CashLedger type='in'
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_payment_and_in_ledger_for_sale(): void
    {
        // Arrange
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => 200_000,
            'payable_type' => 'Sale',
            'payable_id'   => $this->sale->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, CashLedger::where('type', 'in')->count());
    }

    // ---------------------------------------------------------------------------
    // 2. Purchase → creates Payment + CashLedger type='out'
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_creates_payment_and_out_ledger_for_purchase(): void
    {
        // Arrange
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => 100_000,
            'payable_type' => 'Purchase',
            'payable_id'   => $this->purchase->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, CashLedger::where('type', 'out')->count());
    }

    // ---------------------------------------------------------------------------
    // 3. Status → 'paid' when fully paid
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_updates_payment_status_to_paid_when_fully_paid(): void
    {
        // Arrange — pay the full grand_total
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => 500_000,   // equals grand_total
            'payable_type' => 'Sale',
            'payable_id'   => $this->sale->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals('paid', $this->sale->fresh()->payment_status);
    }

    // ---------------------------------------------------------------------------
    // 4. Status → 'partial' when partially paid
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_updates_payment_status_to_partial_when_partially_paid(): void
    {
        // Arrange — pay only part of grand_total
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => 200_000,   // less than 500_000
            'payable_type' => 'Sale',
            'payable_id'   => $this->sale->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals('partial', $this->sale->fresh()->payment_status);
    }

    // ---------------------------------------------------------------------------
    // 5. Balance decreases for Purchase payment
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_decreases_account_balance_for_purchase_payment(): void
    {
        // Arrange
        $amount = 100_000;
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => $amount,
            'payable_type' => 'Purchase',
            'payable_id'   => $this->purchase->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(
            2_000_000 - $amount,
            (float) $this->account->fresh()->balance
        );
    }

    // ---------------------------------------------------------------------------
    // 6. Balance increases for Sale payment
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_increases_account_balance_for_sale_payment(): void
    {
        // Arrange
        $amount = 300_000;
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => $amount,
            'payable_type' => 'Sale',
            'payable_id'   => $this->sale->id,
        ];

        // Act
        $this->action->handle($data);

        // Assert
        $this->assertEquals(
            2_000_000 + $amount,
            (float) $this->account->fresh()->balance
        );
    }

    // ---------------------------------------------------------------------------
    // 7. Exception when balance is insufficient for Purchase payment
    // ---------------------------------------------------------------------------

    #[Test]
    public function it_throws_exception_when_balance_insufficient_for_purchase_payment(): void
    {
        // Arrange — amount exceeds account balance of 2_000_000
        $data = [
            'date'         => now()->format('Y-m-d'),
            'amount'       => 3_000_000,
            'payable_type' => 'Purchase',
            'payable_id'   => $this->purchase->id,
        ];

        // Assert + Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/tidak mencukupi/');

        $this->action->handle($data);
    }
}
