<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Transaction::with(['product.category', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Transform data
        $transactions->getCollection()->transform(function ($txn) {
            return [
                'id' => $txn->id,
                'transaction_date' => $txn->transaction_date->format('Y-m-d'),
                'transaction_date_formatted' => $txn->transaction_date->format('d M Y'),
                'product_code' => $txn->product->code,
                'product_name' => $txn->product->name,
                'category' => $txn->product->category->name ?? '-',
                'type' => $txn->type,
                'quantity' => $txn->quantity,
                'current_stock' => $txn->product->current_stock,
                'status' => $this->getStatus($txn->product->current_stock),
                'notes' => $txn->notes,
                'user_name' => $txn->user->name,
            ];
        });

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $products = Product::with('category')->orderBy('name')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'category' => $p->category->name ?? '-',
                'current_stock' => $p->current_stock,
            ];
        });

        return Inertia::render('Inventory/Index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'products' => $products,
            'filters' => $request->only(['search', 'category', 'date_from', 'date_to', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:masuk,keluar',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        // Check if keluar exceeds current stock
        if ($validated['type'] === 'keluar') {
            $product = Product::findOrFail($validated['product_id']);
            if ($product->current_stock < $validated['quantity']) {
                return back()->with('error', 'Stok tidak mencukupi! Stok saat ini: ' . $product->current_stock);
            }
        }

        $validated['user_id'] = auth()->id();

        Transaction::create($validated);

        $typeLabel = $validated['type'] === 'masuk' ? 'Barang Masuk' : 'Barang Keluar';

        return redirect()->route('inventory.index')
            ->with('success', "Transaksi {$typeLabel} berhasil ditambahkan.");
    }

    private function getStatus(int $stock): string
    {
        if ($stock <= 0) return 'Habis';
        if ($stock <= 10) return 'Hampir Habis';
        return 'Tersedia';
    }
}
