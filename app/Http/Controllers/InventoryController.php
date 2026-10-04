<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function dashboard()
    {
        $days = collect(range(6, 0))->map(fn ($n) => now()->subDays($n)->startOfDay());
        $labels = $days->map(fn ($d) => $d->format('d/m'));
        $revenue = $days->map(fn ($d) => Sale::whereDate('created_at', $d)->sum('total_cents') / 100);
        $categories = Category::withSum('products', 'stock')->get();

        return view('inventory.dashboard', ['productCount' => Product::count(), 'stockValue' => Product::selectRaw('COALESCE(SUM(price_cents * stock),0) AS value')->value('value') / 100, 'monthRevenue' => Sale::where('created_at', '>=', now()->startOfMonth())->sum('total_cents') / 100, 'lowStock' => Product::with('category')->whereColumn('stock', '<=', 'minimum_stock')->get(), 'recentSales' => Sale::with('user')->latest()->limit(5)->get(), 'labels' => $labels, 'revenue' => $revenue, 'categoryLabels' => $categories->pluck('name'), 'categoryStock' => $categories->pluck('products_sum_stock')->map(fn ($n) => (int) $n)]);
    }

    public function products(Request $r)
    {
        $products = Product::with('category')->when($r->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$r->q.'%')->orWhere('sku', 'like', '%'.$r->q.'%')))->when($r->filled('category'), fn ($q) => $q->where('category_id', $r->category))->when($r->boolean('low'), fn ($q) => $q->whereColumn('stock', '<=', 'minimum_stock'))->orderBy('name')->paginate(12)->withQueryString();

        return view('inventory.products', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function storeCategory(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:60|unique:categories']);
        Category::create($data);

        return back()->with('success', 'Categoría creada.');
    }

    public function storeProduct(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:120', 'sku' => 'required|string|max:40|unique:products', 'category_id' => 'required|exists:categories,id', 'price' => 'required|numeric|min:0.01|max:99999.99', 'stock' => 'required|integer|min:0|max:100000', 'minimum_stock' => 'required|integer|min:0|max:100000']);
        DB::transaction(function () use ($data, $r) {
            $data['price_cents'] = (int) round($data['price'] * 100);
            unset($data['price']);
            $p = Product::create($data);
            if ($p->stock) {
                StockMovement::create(['product_id' => $p->id, 'user_id' => $r->user()->id, 'quantity' => $p->stock, 'reason' => 'Stock inicial']);
            }
        });

        return back()->with('success', 'Producto añadido al catálogo.');
    }

    public function sales()
    {
        return view('inventory.sales', ['sales' => Sale::with('user', 'items.product')->latest()->paginate(10), 'products' => Product::where('stock', '>', 0)->orderBy('name')->get()]);
    }

    public function storeSale(Request $r)
    {
        $data = $r->validate(['customer' => 'required|string|max:120', 'items' => 'required|array|min:1|max:30', 'items.*.product_id' => 'required|integer|exists:products,id|distinct', 'items.*.quantity' => 'required|integer|min:1|max:10000']);
        DB::transaction(function () use ($data, $r) {
            $sale = Sale::create(['user_id' => $r->user()->id, 'customer' => $data['customer'], 'total_cents' => 0]);
            $total = 0;
            foreach ($data['items'] as $i) {
                $p = Product::lockForUpdate()->findOrFail($i['product_id']);
                // Conditional update also prevents overselling when SQLite does not support row locks.
                if (! Product::whereKey($p->id)->where('stock', '>=', $i['quantity'])->decrement('stock', $i['quantity'])) {
                    throw ValidationException::withMessages(['items' => 'Stock insuficiente para '.$p->name.'.']);
                }
                SaleItem::create(['sale_id' => $sale->id, 'product_id' => $p->id, 'quantity' => $i['quantity'], 'unit_price_cents' => $p->price_cents]);
                StockMovement::create(['product_id' => $p->id, 'user_id' => $r->user()->id, 'quantity' => -$i['quantity'], 'reason' => 'Venta #'.$sale->id]);
                $total += $p->price_cents * $i['quantity'];
            }
            $sale->update(['total_cents' => $total]);
        });

        return redirect()->route('sales')->with('success', 'Venta registrada y stock actualizado.');
    }

    public function movements()
    {
        return view('inventory.movements', ['movements' => StockMovement::with('product', 'user')->latest()->paginate(15), 'products' => Product::orderBy('name')->get()]);
    }

    public function storeMovement(Request $r)
    {
        $data = $r->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|not_in:0|min:-100000|max:100000', 'reason' => 'required|string|max:180']);
        DB::transaction(function () use ($data, $r) {
            $p = Product::lockForUpdate()->findOrFail($data['product_id']);
            $query = Product::whereKey($p->id);
            if ($data['quantity'] < 0) {
                $query->where('stock', '>=', -$data['quantity']);
            } if (! $query->increment('stock', $data['quantity'])) {
                throw ValidationException::withMessages(['quantity' => 'El stock no puede quedar en negativo.']);
            } StockMovement::create($data + ['user_id' => $r->user()->id]);
        });

        return back()->with('success', 'Movimiento de stock registrado.');
    }
}
