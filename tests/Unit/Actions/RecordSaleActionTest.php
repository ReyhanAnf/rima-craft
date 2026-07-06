<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\RecordSaleAction;
use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Contact;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RecordSaleActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private RecordSaleAction $action;
    private Account $account;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action   = new RecordSaleAction();

        // RecordSaleAction uses Account::first(), so ensure our account
        // is the first one by truncating and force-creating with id=1.
        Account::query()->forceDelete();
        $this->account = Account::forceCreate([
            'id'      => 1,
            'name'    => 'Kas Utama',
            'type'    => 'cash',
            'balance' => 1_000_000,
        ]);

        $this->productA = Product::factory()->create(['current_stock' => 10, 'base_price' => 50_000]);
        $this->productB = Product::factory()->create(['current_stock' => 10, 'base_price' => 75_000]);
    }

    // ---------------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------------

    private function validSaleData(array $overrides = []): array
    {
        return array_merge([
            'date'            => now()->format('Y-m-d'),
            'payment_status'  => 'unpaid',
            'shipping_status' => 'pending',
            'shipping_fee'    => 0,
            'discount'        => 0,
            'items'           => [
                ['product_id' => $this->productA->id, 'qty' => 2, 'price' => 50_000],
            ],
        ], $overrides);
    }

    // ---------------------------------------------------------------------------
    // Tests — Requirements 2.1–2.8
    // ---------------------------------------------------------------------------

    /**
     * Req 2.1 — grand_total == total_amount + shipping_fee - discount
     */
    #[Test]
    public function it_creates_sale_with_correct_grand_total(): void
    {
        $data = $this->validSaleData([
            'shipping_fee' => 15_000,
            'discount'     => 5_000,
            'items'        => [
                ['product_id' => $this->productA->id, 'qty' => 2, 'price' => 50_000],
            ],
        ]);

        $sale = $this->action->handle($data);

        // total_amount = 2 * 50_000 = 100_000
        // grand_total  = 100_000 + 15_000 - 5_000 = 110_000
        $this->assertEquals(100_000, (float) $sale->total_amount);
        $this->assertEquals(110_000, (float) $sale->grand_total);
    }

    /**
     * Req 2.2 — A SaleItem record is created for each item in the items array
     */
    #[Test]
    public function it_creates_sale_items_for_each_item_in_array(): void
    {
        $data = $this->validSaleData([
            'items' => [
                ['product_id' => $this->productA->id, 'qty' => 1, 'price' => 50_000],
                ['product_id' => $this->productB->id, 'qty' => 3, 'price' => 75_000],
            ],
        ]);

        $this->action->handle($data);

        $this->assertEquals(2, SaleItem::count());
    }

    /**
     * Req 2.3 — Product stock is reduced by the purchased quantity after a sale
     */
    #[Test]
    public function it_reduces_product_stock_after_sale(): void
    {
        $initialStock = $this->productA->current_stock; // 10

        $data = $this->validSaleData([
            'items' => [
                ['product_id' => $this->productA->id, 'qty' => 3, 'price' => 50_000],
            ],
        ]);

        $this->action->handle($data);

        $this->assertEquals($initialStock - 3, $this->productA->fresh()->current_stock);
    }

    /**
     * Req 2.4 — A Payment and a CashLedger 'in' entry are created when payment_status is 'paid'
     */
    #[Test]
    public function it_creates_payment_and_ledger_when_paid(): void
    {
        $data = $this->validSaleData([
            'payment_status' => 'paid',
            'account_id'     => $this->account->id,
        ]);

        $this->action->handle($data);

        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, CashLedger::where('type', 'in')->count());
    }

    /**
     * Req 2.5 — Account balance increases by grand_total when payment_status is 'paid'
     */
    #[Test]
    public function it_increases_account_balance_when_paid(): void
    {
        $data = $this->validSaleData([
            'payment_status' => 'paid',
            'account_id'     => $this->account->id,
            'items'          => [
                ['product_id' => $this->productA->id, 'qty' => 2, 'price' => 50_000],
            ],
        ]);

        $sale = $this->action->handle($data);

        // grand_total = 2 * 50_000 = 100_000
        // expected balance = 1_000_000 + 100_000 = 1_100_000
        $expectedBalance = 1_000_000 + (float) $sale->grand_total;
        $this->assertEquals($expectedBalance, (float) $this->account->fresh()->balance);
    }

    /**
     * Req 2.6 — No Payment or CashLedger records are created when payment_status is 'unpaid'
     */
    #[Test]
    public function it_does_not_create_payment_or_ledger_when_unpaid(): void
    {
        $data = $this->validSaleData([
            'payment_status' => 'unpaid',
        ]);

        $this->action->handle($data);

        $this->assertEquals(0, Payment::count());
        $this->assertEquals(0, CashLedger::count());
    }

    /**
     * Req 2.7 — An exception is thrown when requested qty exceeds available stock,
     *            and the exception message contains the product name
     */
    #[Test]
    public function it_throws_exception_when_stock_is_insufficient(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($this->productA->name, '/') . '/');

        $data = $this->validSaleData([
            'items' => [
                ['product_id' => $this->productA->id, 'qty' => 99, 'price' => 50_000],
            ],
        ]);

        $this->action->handle($data);
    }

    /**
     * Req 2.8 — A Contact record with type='customer' is created when save_customer is true
     */
    #[Test]
    public function it_creates_customer_contact_when_save_customer_is_true(): void
    {
        $data = $this->validSaleData([
            'save_customer'  => true,
            'customer_name'  => 'Test Customer',
            'customer_phone' => '08123456789',
        ]);

        $this->action->handle($data);

        $this->assertEquals(1, Contact::where('type', 'customer')->count());
    }
}
