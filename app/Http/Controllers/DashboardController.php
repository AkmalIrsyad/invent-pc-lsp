<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Total products
        $totalProducts = Product::count();

        // Total stock (sum of all current stocks)
        $products = Product::with('transactions')->get();
        $totalStock = $products->sum(function ($product) {
            return $product->current_stock;
        });

        // Barang masuk bulan ini
        $barangMasuk = Transaction::where('type', 'masuk')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('quantity');

        // Barang keluar bulan ini
        $barangKeluar = Transaction::where('type', 'keluar')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('quantity');

        // Grafik transaksi 6 bulan terakhir
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $masuk = Transaction::where('type', 'masuk')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('quantity');

            $keluar = Transaction::where('type', 'keluar')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('quantity');

            $chartData[] = [
                'month' => $month->translatedFormat('M Y'),
                'masuk' => (int) $masuk,
                'keluar' => (int) $keluar,
            ];
        }

        // 5 Transaksi terbaru
        $recentTransactions = Transaction::with(['product', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'product_name' => $txn->product->name,
                    'product_code' => $txn->product->code,
                    'type' => $txn->type,
                    'quantity' => $txn->quantity,
                    'transaction_date' => $txn->transaction_date->format('d M Y'),
                    'user_name' => $txn->user->name,
                    'notes' => $txn->notes,
                ];
            });

        // 5 Stok terendah
        $lowStock = $products->sortBy(function ($product) {
            return $product->current_stock;
        })->take(5)->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'current_stock' => $product->current_stock,
                'category' => $product->category->name ?? '-',
            ];
        })->values();

        // 5 Stok tertinggi
        $highStock = $products->sortByDesc(function ($product) {
            return $product->current_stock;
        })->take(5)->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'current_stock' => $product->current_stock,
                'category' => $product->category->name ?? '-',
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalStock' => $totalStock,
                'barangMasuk' => (int) $barangMasuk,
                'barangKeluar' => (int) $barangKeluar,
            ],
            'chartData' => $chartData,
            'recentTransactions' => $recentTransactions,
            'lowStock' => $lowStock,
            'highStock' => $highStock,
        ]);
    }
}
