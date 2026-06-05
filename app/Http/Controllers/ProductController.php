<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category->name ?? '-',
                'category_id' => $product->category_id,
                'description' => $product->description,
                'photo' => $product->photo ? Storage::url($product->photo) : null,
                'initial_stock' => $product->initial_stock,
                'current_stock' => $product->current_stock,
            ];
        });

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'initial_stock' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'transactions.user']);

        $transactions = $product->transactions()
            ->with('user')
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'type' => $txn->type,
                    'quantity' => $txn->quantity,
                    'transaction_date' => $txn->transaction_date->format('d M Y'),
                    'notes' => $txn->notes,
                    'user_name' => $txn->user->name,
                ];
            });

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category->name ?? '-',
                'category_id' => $product->category_id,
                'description' => $product->description,
                'photo' => $product->photo ? Storage::url($product->photo) : null,
                'initial_stock' => $product->initial_stock,
                'current_stock' => $product->current_stock,
                'created_at' => $product->created_at->format('d M Y'),
            ],
            'transactions' => $transactions,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'initial_stock' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }
}
