<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\AdjustStockAction;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AdjustStockActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private AdjustStockAction $action;
    private Material $material;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // AdjustStockAction uses auth()->id(), so we need an authenticated user
        $this->actingAs(User::factory()->create());

        $this->material = Material::factory()->create(['current_stock' => 10]);
        $this->product  = Product::factory()->create(['current_stock' => 20]);

        $this->action = new AdjustStockAction();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function materialData(array $overrides = []): array
    {
        return array_merge([
            'adjustable_type' => 'material',
            'adjustable_id'   => $this->material->id,
            'actual_stock'    => 15,
            'reason'          => 'Stok opname',
        ], $overrides);
    }

    private function productData(array $overrides = []): array
    {
        return array_merge([
            'adjustable_type' => 'product',
            'adjustable_id'   => $this->product->id,
            'actual_stock'    => 25,
            'reason'          => 'Stok opname',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Test Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_creates_adjustment_and_updates_material_stock(): void
    {
        // Arrange
        $actualStock = 15;
        $data        = $this->materialData(['actual_stock' => $actualStock]);

        // Act
        $adjustment = $this->action->handle($data);

        // Assert
        $this->assertInstanceOf(StockAdjustment::class, $adjustment);
        $this->assertEquals(1, StockAdjustment::count());
        $this->assertEquals($actualStock, $this->material->fresh()->current_stock);
    }

    #[Test]
    public function it_creates_adjustment_and_updates_product_stock(): void
    {
        // Arrange
        $actualStock = 25;
        $data        = $this->productData(['actual_stock' => $actualStock]);

        // Act
        $adjustment = $this->action->handle($data);

        // Assert
        $this->assertInstanceOf(StockAdjustment::class, $adjustment);
        $this->assertEquals(1, StockAdjustment::count());
        $this->assertEquals($actualStock, $this->product->fresh()->current_stock);
    }

    #[Test]
    public function it_stores_correct_quantity_difference_on_adjustment_record(): void
    {
        // Arrange
        $previousStock = $this->material->current_stock; // 10
        $actualStock   = 18;
        $data          = $this->materialData(['actual_stock' => $actualStock]);

        // Act
        $adjustment = $this->action->handle($data);

        // Assert
        $expectedDifference = $actualStock - $previousStock; // 8
        $this->assertEquals($expectedDifference, $adjustment->quantity_difference);
    }

    #[Test]
    public function it_throws_exception_when_actual_stock_equals_current_stock(): void
    {
        // Arrange — actual == current_stock (10)
        $data = $this->materialData(['actual_stock' => $this->material->current_stock]);

        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tidak ada perubahan stok.');

        $this->action->handle($data);
    }

    #[Test]
    public function it_does_not_create_record_when_no_change_in_stock(): void
    {
        // Arrange — actual == current_stock (10)
        $data = $this->materialData(['actual_stock' => $this->material->current_stock]);

        // Act — suppress exception
        try {
            $this->action->handle($data);
        } catch (\Exception) {
            // expected
        }

        // Assert — no adjustment record must be persisted
        $this->assertEquals(0, StockAdjustment::count());
    }
}
