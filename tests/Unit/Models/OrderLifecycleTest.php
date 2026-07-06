<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Region;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    private int $provinceId;
    private int $cityId;

    protected function setUp(): void
    {
        parent::setUp();

        // code column is varchar(15) — keep values short and unique
        $province = Region::create([
            'code'      => 'P-' . substr(md5(uniqid()), 0, 8),
            'parent_id' => null,
            'type'      => 'province',
            'name'      => 'Test Province',
        ]);

        $city = Region::create([
            'code'      => 'C-' . substr(md5(uniqid()), 0, 8),
            'parent_id' => $province->id,
            'type'      => 'city',
            'name'      => 'Test City',
        ]);

        $this->provinceId = $province->id;
        $this->cityId     = $city->id;
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function makeOrder(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'province_id' => $this->provinceId,
            'city_id'     => $this->cityId,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Requirements 9.1–9.11
    // -------------------------------------------------------------------------

    /**
     * Req 9.1 — Order number is auto-generated on creation with format ORD-YYYYMMDD-NNN
     */
    #[Test]
    public function it_auto_generates_order_number_with_correct_format(): void
    {
        $order = $this->makeOrder();

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{3}$/', $order->order_number);
    }

    /**
     * Req 9.2 — Two orders created on the same day receive distinct order numbers
     */
    #[Test]
    public function it_generates_unique_order_numbers_on_same_day(): void
    {
        $orderA = $this->makeOrder();
        $orderB = $this->makeOrder();

        $this->assertNotEquals($orderA->order_number, $orderB->order_number);
    }

    /**
     * Req 9.3 — confirm() sets status to 'confirmed' and records confirmed_at timestamp
     */
    #[Test]
    public function it_confirm_sets_status_and_confirmed_at(): void
    {
        $order = $this->makeOrder(['status' => 'pending']);

        $order->confirm();

        $fresh = $order->fresh();
        $this->assertEquals('confirmed', $fresh->status);
        $this->assertNotNull($fresh->confirmed_at);
    }

    /**
     * Req 9.4 — markProcessing() sets status to 'processing'
     */
    #[Test]
    public function it_mark_processing_sets_status(): void
    {
        $order = $this->makeOrder(['status' => 'confirmed']);

        $order->markProcessing();

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /**
     * Req 9.5 — markShipped() sets status to 'shipped' and records shipped_at timestamp
     */
    #[Test]
    public function it_mark_shipped_sets_status_and_shipped_at(): void
    {
        $order = $this->makeOrder(['status' => 'processing']);

        $order->markShipped();

        $fresh = $order->fresh();
        $this->assertEquals('shipped', $fresh->status);
        $this->assertNotNull($fresh->shipped_at);
    }

    /**
     * Req 9.6 — complete() sets status to 'completed' and records completed_at timestamp
     */
    #[Test]
    public function it_complete_sets_status_and_completed_at(): void
    {
        $order = $this->makeOrder(['status' => 'shipped']);

        $order->complete();

        $fresh = $order->fresh();
        $this->assertEquals('completed', $fresh->status);
        $this->assertNotNull($fresh->completed_at);
    }

    /**
     * Req 9.7 — cancel() sets status to 'cancelled', records cancelled_at, and stores the reason
     */
    #[Test]
    public function it_cancel_sets_status_cancelled_at_and_reason(): void
    {
        $order = $this->makeOrder(['status' => 'pending']);

        $order->cancel('Stok habis');

        $fresh = $order->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertNotNull($fresh->cancelled_at);
        $this->assertEquals('Stok habis', $fresh->cancellation_reason);
    }

    /**
     * Req 9.8 — isPending() returns true when status is 'pending'
     */
    #[Test]
    public function it_is_pending_returns_true_for_pending_order(): void
    {
        $order = $this->makeOrder(['status' => 'pending']);

        $this->assertTrue($order->isPending());
    }

    /**
     * Req 9.9 — isConfirmed() returns true when status is 'confirmed'
     */
    #[Test]
    public function it_is_confirmed_returns_true_for_confirmed_order(): void
    {
        $order = $this->makeOrder(['status' => 'confirmed']);

        $this->assertTrue($order->isConfirmed());
    }

    /**
     * Req 9.10 — isPaid() returns true when payment_status is 'paid'
     */
    #[Test]
    public function it_is_paid_returns_true_when_payment_status_is_paid(): void
    {
        $order = $this->makeOrder(['payment_status' => 'paid']);

        $this->assertTrue($order->isPaid());
    }

    /**
     * Req 9.11 — isPartiallyPaid() returns true when payment_status is 'partial'
     */
    #[Test]
    public function it_is_partially_paid_returns_true_when_payment_status_is_partial(): void
    {
        $order = $this->makeOrder(['payment_status' => 'partial']);

        $this->assertTrue($order->isPartiallyPaid());
    }
}
