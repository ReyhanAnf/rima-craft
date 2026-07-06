<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\RecordTransactionAction;
use App\Models\Account;
use App\Models\CashLedger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RecordTransactionActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private RecordTransactionAction $action;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        // RecordTransactionAction hardcodes Account::findOrFail(1), so we must
        // ensure the account exists with id=1 and the correct balance.
        // Use forceDelete + forceCreate to handle pre-existing seeded accounts.
        Account::query()->forceDelete();
        $this->account = Account::forceCreate([
            'id'      => 1,
            'name'    => 'Kas Utama',
            'type'    => 'cash',
            'balance' => 500_000,
        ]);

        $this->action = new RecordTransactionAction();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function validTransactionData(array $overrides = []): array
    {
        return array_merge([
            'date'          => now()->format('Y-m-d'),
            'type'          => 'in',
            'amount'        => 100_000,
            'description'   => 'Test transaksi manual',
            'payment_label' => 'Cash',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Test Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_creates_in_ledger_and_increases_balance(): void
    {
        // Arrange
        $data = $this->validTransactionData(['type' => 'in', 'amount' => 100_000]);

        // Act
        $ledger = $this->action->handle($data);

        // Assert
        $this->assertInstanceOf(CashLedger::class, $ledger);
        $this->assertEquals('in', $ledger->type);
        $this->assertEquals(1, CashLedger::count());
        $this->assertEquals('600000.00', $this->account->fresh()->balance);
    }

    #[Test]
    public function it_creates_out_ledger_and_decreases_balance_when_sufficient(): void
    {
        // Arrange
        $data = $this->validTransactionData(['type' => 'out', 'amount' => 100_000]);

        // Act
        $ledger = $this->action->handle($data);

        // Assert
        $this->assertInstanceOf(CashLedger::class, $ledger);
        $this->assertEquals('out', $ledger->type);
        $this->assertEquals(1, CashLedger::count());
        $this->assertEquals('400000.00', $this->account->fresh()->balance);
    }

    #[Test]
    public function it_sets_correct_balance_after_for_in_transaction(): void
    {
        // Arrange
        $amount = 200_000;
        $data   = $this->validTransactionData(['type' => 'in', 'amount' => $amount]);

        // Act
        $ledger = $this->action->handle($data);

        // Assert
        $expectedBalanceAfter = 500_000 + $amount; // 700_000
        $this->assertEquals((string) number_format($expectedBalanceAfter, 2, '.', ''), $ledger->balance_after);
    }

    #[Test]
    public function it_sets_correct_balance_after_for_out_transaction(): void
    {
        // Arrange
        $amount = 150_000;
        $data   = $this->validTransactionData(['type' => 'out', 'amount' => $amount]);

        // Act
        $ledger = $this->action->handle($data);

        // Assert
        $expectedBalanceAfter = 500_000 - $amount; // 350_000
        $this->assertEquals((string) number_format($expectedBalanceAfter, 2, '.', ''), $ledger->balance_after);
    }

    #[Test]
    public function it_throws_exception_when_out_amount_exceeds_balance(): void
    {
        // Arrange
        $data = $this->validTransactionData(['type' => 'out', 'amount' => 600_000]);

        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Saldo kas 'Kas Utama' tidak mencukupi!");

        $this->action->handle($data);
    }

    #[Test]
    public function it_does_not_modify_balance_when_exception_is_thrown(): void
    {
        // Arrange — amount exceeds balance to force exception
        $data = $this->validTransactionData(['type' => 'out', 'amount' => 999_999]);

        // Act — suppress exception
        try {
            $this->action->handle($data);
        } catch (\Exception) {
            // expected
        }

        // Assert — balance must remain untouched
        $this->assertEquals('500000.00', $this->account->fresh()->balance);
        $this->assertEquals(0, CashLedger::count());
    }
}
