<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\Product\StoreProductRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreProductRequestTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_passes_with_valid_name_base_price_and_stock(): void
    {
        $data = [
            'name'          => 'Kerajinan Rotan',
            'base_price'    => 150000,
            'current_stock' => 10,
        ];

        $validator = Validator::make($data, (new StoreProductRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function it_fails_without_name_or_with_negative_base_price(): void
    {
        // Missing name
        $missingName = [
            'base_price'    => 100000,
            'current_stock' => 5,
        ];

        $validator = Validator::make($missingName, (new StoreProductRequest)->rules());
        $this->assertTrue($validator->fails(), 'Should fail when name is missing');

        // Negative base_price
        $negativePrice = [
            'name'          => 'Produk A',
            'base_price'    => -1,
            'current_stock' => 5,
        ];

        $validator = Validator::make($negativePrice, (new StoreProductRequest)->rules());
        $this->assertTrue($validator->fails(), 'Should fail when base_price is negative');
    }

    #[Test]
    public function it_fails_when_current_stock_is_non_integer(): void
    {
        // Float value
        $floatStock = [
            'name'          => 'Produk B',
            'base_price'    => 50000,
            'current_stock' => 1.5,
        ];

        $validator = Validator::make($floatStock, (new StoreProductRequest)->rules());
        $this->assertTrue($validator->fails(), 'Should fail when current_stock is a float (1.5)');

        // String value
        $stringStock = [
            'name'          => 'Produk C',
            'base_price'    => 50000,
            'current_stock' => 'abc',
        ];

        $validator = Validator::make($stringStock, (new StoreProductRequest)->rules());
        $this->assertTrue($validator->fails(), 'Should fail when current_stock is a string');
    }
}
