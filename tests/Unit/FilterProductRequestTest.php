<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\Catalog\FilterProductRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilterProductRequestTest extends TestCase
{
    #[Test]
    public function it_accepts_valid_stock_values(): void
    {
        $request = new FilterProductRequest();

        foreach (['semua', 'tersedia', 'habis'] as $value) {
            $validator = Validator::make(
                ['stock' => $value],
                $request->rules()
            );

            $this->assertTrue($validator->passes(), "Stock value '{$value}' should be valid");
        }
    }

    #[Test]
    public function it_rejects_invalid_stock_values(): void
    {
        $request = new FilterProductRequest();

        foreach (['invalid', 'all', 'available', 'out_of_stock', '123'] as $value) {
            $validator = Validator::make(
                ['stock' => $value],
                $request->rules()
            );

            $this->assertTrue($validator->fails(), "Stock value '{$value}' should be invalid");
        }
    }

    #[Test]
    public function it_accepts_search_with_allowed_chars(): void
    {
        $request = new FilterProductRequest();

        $validSearches = [
            'kerajinan',
            'kursi rotan',
            'anyaman-bambu',
            'meja.kayu',
            'produk, barang',
            'apa ini?',
            'wow!',
            'AbCdEf123',
        ];

        foreach ($validSearches as $search) {
            $validator = Validator::make(
                ['search' => $search],
                $request->rules()
            );

            $this->assertTrue($validator->passes(), "Search '{$search}' should be valid");
        }
    }

    #[Test]
    public function it_rejects_search_with_special_chars(): void
    {
        $request = new FilterProductRequest();

        $invalidSearches = [
            '<script>alert(1)</script>',
            "test' OR '1'='1",
            'test; DROP TABLE',
            'data<script>',
            'hack<>data',
        ];

        foreach ($invalidSearches as $search) {
            $validator = Validator::make(
                ['search' => $search],
                $request->rules()
            );

            $this->assertTrue($validator->fails(), "Search '{$search}' should be invalid");
        }
    }

    #[Test]
    public function it_rejects_search_longer_than_100_chars(): void
    {
        $request = new FilterProductRequest();

        $validator = Validator::make(
            ['search' => str_repeat('a', 101)],
            $request->rules()
        );

        $this->assertTrue($validator->fails(), 'Search longer than 100 chars should be invalid');
    }

    #[Test]
    public function it_accepts_null_search(): void
    {
        $request = new FilterProductRequest();

        $validator = Validator::make(
            ['search' => null],
            $request->rules()
        );

        $this->assertTrue($validator->passes(), 'Null search should be valid');
    }
}
