<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductRegionPrice;
use App\Models\ProductUserPrice;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ProductGetPriceForUserTest extends TestCase
{
    use CreatesTestData;

    private Product $product;
    private Region $province;
    private Region $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create([
            'base_price'     => 100_000,
            'reseller_price' => 80_000,
        ]);

        $this->province = Region::create([
            'name'      => 'Jawa Tengah',
            'type'      => 'province',
            'parent_id' => null,
        ]);

        $this->city = Region::create([
            'name'      => 'Kota Semarang',
            'type'      => 'city',
            'parent_id' => $this->province->id,
        ]);
    }

    /**
     * Req 8.1 — User-specific price is the highest priority
     */
    #[Test]
    public function it_returns_user_specific_price_as_highest_priority(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        ProductUserPrice::create([
            'product_id' => $this->product->id,
            'user_id'    => $reseller->id,
            'price'      => 70_000,
        ]);

        $price = $this->product->getPriceForUser($reseller);

        $this->assertEquals(70_000.0, $price);
    }

    /**
     * Req 8.2 — Reseller gets city-level region price when city has a reseller_price set
     */
    #[Test]
    public function it_returns_reseller_region_city_price_for_reseller_with_city_region(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        ProductRegionPrice::create([
            'product_id'     => $this->product->id,
            'region_id'      => $this->city->id,
            'base_price'     => 95_000,
            'reseller_price' => 75_000,
        ]);

        $price = $this->product->getPriceForUser($reseller, $this->city);

        $this->assertEquals(75_000.0, $price);
    }

    /**
     * Req 8.3 — Falls back to province region price when city has no price
     */
    #[Test]
    public function it_falls_back_to_province_region_price_when_city_has_no_price(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        ProductRegionPrice::create([
            'product_id'     => $this->product->id,
            'region_id'      => $this->province->id,
            'base_price'     => 93_000,
            'reseller_price' => 73_000,
        ]);

        // Pass city — no city price exists, should fall back to province
        $price = $this->product->getPriceForUser($reseller, $this->city);

        $this->assertEquals(73_000.0, $price);
    }

    /**
     * Req 8.4 — Returns default product reseller_price when no region price is set
     */
    #[Test]
    public function it_returns_default_reseller_price_when_no_region_price(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        $price = $this->product->getPriceForUser($reseller);

        $this->assertEquals(80_000.0, $price);
    }

    /**
     * Req 8.5 — Returns base_price for a regular customer user
     */
    #[Test]
    public function it_returns_base_price_for_regular_customer(): void
    {
        $customer = $this->createUserWithRole('customer');

        $price = $this->product->getPriceForUser($customer);

        $this->assertEquals(100_000.0, $price);
    }

    /**
     * Req 8.6 — Returns base_price when user is null
     */
    #[Test]
    public function it_returns_base_price_when_user_is_null(): void
    {
        $price = $this->product->getPriceForUser(null);

        $this->assertEquals(100_000.0, $price);
    }
}
