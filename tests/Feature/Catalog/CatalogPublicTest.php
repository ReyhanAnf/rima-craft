<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CatalogPublicTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Product> */
    private $products;

    /** @var Product */
    private Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Create several products in stock (current_stock = 10..100)
        $this->products = Product::factory()->count(3)->create();

        // Create one out-of-stock product
        $this->outOfStockProduct = Product::factory()->out_of_stock()->create();
    }

    #[Test]
    public function it_catalog_page_is_publicly_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filter_returns_only_matching_products_by_search(): void
    {
        // Create a product with a known, unique name
        $uniqueProduct = Product::factory()->create([
            'name' => 'Kerajinan Tangan Eksklusif Unik',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/katalog/filter?search=Kerajinan+Tangan+Eksklusif+Unik');

        $response->assertStatus(200);

        $data = $response->json('products');

        // All returned products must contain the search term
        foreach ($data as $product) {
            $this->assertStringContainsStringIgnoringCase(
                'Kerajinan',
                $product['name'],
            );
        }

        // The unique product must appear in the results
        $ids = array_column($data, 'id');
        $this->assertContains($uniqueProduct->id, $ids);
    }

    #[Test]
    public function it_filter_returns_only_in_stock_products(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/katalog/filter?stock=tersedia');

        $response->assertStatus(200);

        $data = $response->json('products');

        $this->assertNotEmpty($data, 'Expected at least one in-stock product in the response.');

        foreach ($data as $product) {
            $this->assertGreaterThan(
                0,
                $product['current_stock'],
                "Product ID {$product['id']} should have current_stock > 0 for stock=tersedia filter.",
            );
        }

        // Out-of-stock product must NOT appear
        $ids = array_column($data, 'id');
        $this->assertNotContains(
            $this->outOfStockProduct->id,
            $ids,
            'Out-of-stock product should not appear in tersedia filter.',
        );
    }

    #[Test]
    public function it_filter_returns_only_out_of_stock_products(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/katalog/filter?stock=habis');

        $response->assertStatus(200);

        $data = $response->json('products');

        $this->assertNotEmpty($data, 'Expected at least one out-of-stock product in the response.');

        foreach ($data as $product) {
            $this->assertEquals(
                0,
                $product['current_stock'],
                "Product ID {$product['id']} should have current_stock == 0 for stock=habis filter.",
            );
        }

        // Our known out-of-stock product must appear
        $ids = array_column($data, 'id');
        $this->assertContains(
            $this->outOfStockProduct->id,
            $ids,
            'Known out-of-stock product should appear in habis filter.',
        );
    }

    #[Test]
    public function it_filter_rejects_xss_in_search(): void
    {
        $xssPayload = '<script>alert(1)</script>';

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/katalog/filter?search=' . urlencode($xssPayload));

        $response->assertStatus(422);
    }
}
