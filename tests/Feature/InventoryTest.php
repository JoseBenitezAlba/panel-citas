<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock = 5): Product
    {
        return Product::create(['name' => 'Teclado', 'sku' => 'KEY-01', 'category_id' => Category::create(['name' => 'Periféricos'])->id, 'price_cents' => 1999, 'stock' => $stock, 'minimum_stock' => 2]);
    }

    public function test_all_pages_render_for_admin_and_employee(): void
    {
        $this->seed();
        foreach (['admin@example.com', 'empleado@example.com'] as $email) {
            $this->actingAs(User::where('email', $email)->first());
            foreach (['/panel', '/productos', '/ventas', '/movimientos'] as $path) {
                $this->get($path)->assertOk()->assertSee('stockia');
            }
        }
    }

    public function test_employee_cannot_change_catalog_or_adjust_stock(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create());
        $this->post('/productos', [])->assertForbidden();
        $this->post('/categorias', ['name' => 'Audio'])->assertForbidden();
        $this->post('/movimientos', ['product_id' => $p->id, 'quantity' => 10, 'reason' => 'Reposición'])->assertForbidden();
        $this->assertSame(5, $p->fresh()->stock);
    }

    public function test_sale_uses_server_prices_and_reduces_stock(): void
    {
        $p = $this->product();
        $u = User::factory()->create();
        $this->actingAs($u)->post('/ventas', ['customer' => 'Cliente', 'total_cents' => 1, 'items' => [['product_id' => $p->id, 'quantity' => 2, 'unit_price_cents' => 1]]])->assertRedirect('/ventas');
        $this->assertSame(3, $p->fresh()->stock);
        $this->assertSame(3998, Sale::first()->total_cents);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $p->id, 'quantity' => -2, 'user_id' => $u->id]);
    }

    public function test_overselling_rolls_back_every_line(): void
    {
        $p = $this->product();
        $q = Product::create(['name' => 'Ratón', 'sku' => 'MOUSE', 'category_id' => $p->category_id, 'price_cents' => 1200, 'stock' => 1, 'minimum_stock' => 2]);
        $this->actingAs(User::factory()->create())->post('/ventas', ['customer' => 'Cliente', 'items' => [['product_id' => $p->id, 'quantity' => 2], ['product_id' => $q->id, 'quantity' => 2]]])->assertSessionHasErrors('items');
        $this->assertSame(5, $p->fresh()->stock);
        $this->assertSame(1, $q->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_duplicate_products_and_invalid_quantities_are_rejected(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create());
        $this->post('/ventas', ['customer' => 'Cliente', 'items' => [['product_id' => $p->id, 'quantity' => 1], ['product_id' => $p->id, 'quantity' => 1]]])->assertSessionHasErrors();
        $this->post('/ventas', ['customer' => 'Cliente', 'items' => [['product_id' => $p->id, 'quantity' => 0]]])->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_admin_can_restock_but_not_make_stock_negative(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->admin()->create());
        $this->post('/movimientos', ['product_id' => $p->id, 'quantity' => 3, 'reason' => 'Reposición'])->assertSessionHasNoErrors();
        $this->assertSame(8, $p->fresh()->stock);
        $this->post('/movimientos', ['product_id' => $p->id, 'quantity' => -9, 'reason' => 'Ajuste'])->assertSessionHasErrors('quantity');
        $this->assertSame(8, $p->fresh()->stock);
    }

    public function test_product_creation_records_initial_stock_and_unique_sku(): void
    {
        $c = Category::create(['name' => 'Audio']);
        $this->actingAs(User::factory()->admin()->create());
        $data = ['name' => 'Altavoz', 'sku' => 'AUD-1', 'category_id' => $c->id, 'price' => '29.99', 'stock' => 10, 'minimum_stock' => 2];
        $this->post('/productos', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['sku' => 'AUD-1', 'price_cents' => 2999, 'stock' => 10]);
        $this->assertDatabaseHas('stock_movements', ['quantity' => 10, 'reason' => 'Stock inicial']);
        $this->post('/productos', $data)->assertSessionHasErrors('sku');
    }

    public function test_seeding_is_idempotent_and_stock_matches_ledger(): void
    {
        $this->seed();
        $sales = Sale::count();
        $movements = StockMovement::count();
        $p = Product::first();
        $p->update(['stock' => $p->stock + 1]);
        StockMovement::create(['product_id' => $p->id, 'user_id' => User::first()->id, 'quantity' => 1, 'reason' => 'Ajuste']);
        $this->seed();
        $this->assertDatabaseCount('sales', $sales);
        $this->assertDatabaseCount('stock_movements', $movements + 1);
        foreach (Product::all() as $product) {
            $this->assertSame($product->stock, (int) StockMovement::where('product_id', $product->id)->sum('quantity'));
        }
    }

    public function test_filters_and_empty_states(): void
    {
        $this->product();
        $this->actingAs(User::factory()->create())->get('/productos?q=no-existe')->assertOk()->assertSee('No hay productos');
        $this->get('/panel')->assertOk()->assertSee('Tu primera venta');
    }
}
