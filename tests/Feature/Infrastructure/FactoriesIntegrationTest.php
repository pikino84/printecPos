<?php

namespace Tests\Feature\Infrastructure;

use App\Models\CartSession;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FactoriesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Salvaguarda crítica: si esta aserción falla, los tests están a punto de
     * destruir la BD de desarrollo. NUNCA quitar este test.
     */
    public function test_tests_apuntan_a_la_db_de_test(): void
    {
        $this->assertSame('dev_pos_printec_test', DB::connection()->getDatabaseName());
        $this->assertSame('mysql', DB::connection()->getName());
    }

    public function test_partner_factory_crea_un_partner_activo(): void
    {
        $partner = Partner::factory()->create();

        $this->assertDatabaseHas('partners', ['id' => $partner->id]);
        $this->assertTrue($partner->is_active);
        $this->assertSame('Asociado', $partner->type);
    }

    public function test_user_factory_inyecta_partner_id(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->partner_id);
        $this->assertInstanceOf(Partner::class, Partner::find($user->partner_id));
    }

    public function test_product_factory_crea_cadena_completa(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->partner_id);
        $this->assertSame($product->partner_id, $product->owner_id, 'partner_id y owner_id deben coincidir por default');
        $this->assertNotNull($product->product_category_id);
        $this->assertNotNull($product->created_by);
    }

    public function test_product_factory_estado_per_meter(): void
    {
        $product = Product::factory()->perMeter()->create();

        $this->assertTrue($product->isPerMeter());
        $this->assertSame(Product::UNIT_TYPE_METRO_CUADRADO, $product->unit_type);
    }

    public function test_quote_factory_with_items_calcula_totales(): void
    {
        $quote = Quote::factory()->withItems(3)->create();

        $this->assertCount(3, $quote->items);
        $this->assertGreaterThan(0, (float) $quote->fresh()->subtotal);
        $this->assertGreaterThan(0, (float) $quote->fresh()->total);
    }

    public function test_quote_item_factory_recalcula_subtotal_via_boot(): void
    {
        $item = QuoteItem::factory()->create([
            'quantity' => 4,
            'unit_price' => 100.00,
        ]);

        $this->assertSame('400.00', $item->subtotal);
    }

    public function test_cadena_completa_partner_a_cart(): void
    {
        $partner = Partner::factory()->mixto()->create();
        $user = User::factory()->forPartner($partner)->create();
        $category = ProductCategory::factory()->create(['partner_id' => $partner->id]);
        $product = Product::factory()->create([
            'partner_id' => $partner->id,
            'owner_id' => $partner->id,
            'created_by' => $user->id,
            'product_category_id' => $category->id,
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $warehouse = ProductWarehouse::factory()->create(['partner_id' => $partner->id]);

        $quote = Quote::factory()->create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
        ]);
        QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 2,
            'unit_price' => 150.00,
        ]);

        $cart = CartSession::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $this->assertSame($partner->id, $user->partner_id);
        $this->assertSame($partner->id, $product->partner_id);
        $this->assertSame($product->id, $variant->product_id);
        $this->assertSame($variant->id, $cart->variant_id);
        $this->assertCount(1, $quote->items);
        $this->assertSame('300.00', $quote->items->first()->subtotal);
    }
}
