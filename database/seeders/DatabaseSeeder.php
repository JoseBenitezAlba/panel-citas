<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::firstOrCreate(['email' => 'admin@example.com'], ['name' => 'Ana García', 'password' => 'password']);
            $admin->forceFill(['role' => 'admin'])->save();
            $staff = User::firstOrCreate(['email' => 'empleado@example.com'], ['name' => 'Pablo Martín', 'password' => 'password']);
            $staff->forceFill(['role' => 'empleado'])->save();
            // Never duplicate demo sales or reset stock on a redeploy.
            if (Product::exists()) {
                return;
            }
            $rows = [['Audio', 'Auriculares Studio', 'AUD-001', 8990, 24, 6], ['Audio', 'Altavoz portátil', 'AUD-002', 4990, 4, 5], ['Accesorios', 'Hub USB-C 7 en 1', 'ACC-001', 3990, 18, 5], ['Accesorios', 'Cargador 65W', 'ACC-002', 2990, 3, 5], ['Periféricos', 'Teclado mecánico', 'PER-001', 7490, 32, 8], ['Periféricos', 'Ratón inalámbrico', 'PER-002', 2490, 40, 10], ['Accesorios', 'Soporte de portátil', 'ACC-003', 3490, 16, 5], ['Audio', 'Micrófono USB', 'AUD-003', 6990, 12, 4], ['Periféricos', 'Monitor 24 pulgadas', 'PER-003', 14990, 2, 3], ['Accesorios', 'Cable USB-C', 'ACC-004', 1290, 60, 15], ['Periféricos', 'Alfombrilla XL', 'PER-004', 1990, 22, 6], ['Audio', 'Auriculares deportivos', 'AUD-004', 3590, 15, 5]];
            foreach ($rows as [$category,$name,$sku,$price,$stock,$minimum]) {
                $c = Category::firstOrCreate(['name' => $category]);
                $p = Product::create(['category_id' => $c->id, 'name' => $name, 'sku' => $sku, 'price_cents' => $price, 'stock' => $stock, 'minimum_stock' => $minimum]);
                StockMovement::create(['product_id' => $p->id, 'user_id' => $admin->id, 'quantity' => $stock, 'reason' => 'Inventario inicial de demo', 'created_at' => now()->subDays(14)->startOfDay()]);
            }
            $products = Product::all();
            for ($day = 13; $day >= 0; $day--) {
                for ($j = 0; $j < 2 + ($day % 3); $j++) {
                    $p = $products[($day + $j) % $products->count()];
                    $time = now()->subDays($day)->setTime(10 + $j * 2, 15);
                    $amount = $p->price_cents;
                    // Historic stock receipts and sales balance each other: visible stock matches the ledger.
                    StockMovement::create(['product_id' => $p->id, 'user_id' => $admin->id, 'quantity' => 1, 'reason' => 'Reposición de demo', 'created_at' => $time->copy()->subMinutes(5)]);
                    $s = Sale::create(['user_id' => $j % 2 ? $staff->id : $admin->id, 'customer' => ['Marta Ruiz', 'Luis Pérez', 'Venta en tienda', 'Carmen López'][$j % 4], 'total_cents' => $amount, 'created_at' => $time]);
                    SaleItem::create(['sale_id' => $s->id, 'product_id' => $p->id, 'quantity' => 1, 'unit_price_cents' => $amount]);
                    StockMovement::create(['product_id' => $p->id, 'user_id' => $s->user_id, 'quantity' => -1, 'reason' => 'Venta #'.$s->id, 'created_at' => $time]);
                }
            }
        });
    }
}
