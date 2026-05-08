<?php

namespace Tests\Unit\Models;

use App\Models\CartSession;
use App\Models\Product;
use App\Models\QuoteItem;
use PHPUnit\Framework\TestCase;

class ProductPerMeterTest extends TestCase
{
    public function test_product_without_unit_type_is_not_per_meter(): void
    {
        $product = new Product(['unit_type' => null]);

        $this->assertFalse($product->isPerMeter());
    }

    public function test_product_with_metro_cuadrado_is_per_meter(): void
    {
        $product = new Product(['unit_type' => Product::UNIT_TYPE_METRO_CUADRADO]);

        $this->assertTrue($product->isPerMeter());
    }

    public function test_product_with_metro_lineal_is_per_meter(): void
    {
        $product = new Product(['unit_type' => Product::UNIT_TYPE_METRO_LINEAL]);

        $this->assertTrue($product->isPerMeter());
    }

    public function test_product_with_unknown_unit_type_is_not_per_meter(): void
    {
        $product = new Product(['unit_type' => 'gallons']);

        $this->assertFalse($product->isPerMeter());
    }

    public function test_quote_item_quantity_preserves_decimals(): void
    {
        $item = new QuoteItem;
        $item->quantity = 1.8;

        // decimal:2 cast formatea como string con 2 decimales.
        $this->assertSame('1.80', (string) $item->quantity);
    }

    public function test_quote_item_subtotal_calculated_with_decimal_quantity(): void
    {
        $item = new QuoteItem;
        $item->quantity = 1.8;
        $item->unit_price = 100;

        $subtotal = (float) $item->quantity * (float) $item->unit_price;

        $this->assertEqualsWithDelta(180.0, $subtotal, 0.01);
    }

    public function test_cart_session_quantity_preserves_decimals(): void
    {
        $cart = new CartSession;
        $cart->quantity = 2.5;

        $this->assertSame('2.50', (string) $cart->quantity);
    }
}
