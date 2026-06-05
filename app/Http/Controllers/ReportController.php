<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Transaction::with(['product.category', 'user']);

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

        $allTransactions = (clone $query)->get();

        // Stats
        $totalTransactions = $allTransactions->count();
        $totalMasuk = $allTransactions->where('type', 'masuk')->sum('quantity');
        $totalKeluar = $allTransactions->where('type', 'keluar')->sum('quantity');

        // Paginated transactions
        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $transactions->getCollection()->transform(function ($txn) {
            return [
                'id' => $txn->id,
                'transaction_date' => $txn->transaction_date->format('d M Y'),
                'product_code' => $txn->product->code,
                'product_name' => $txn->product->name,
                'category' => $txn->product->category->name ?? '-',
                'type' => $txn->type,
                'quantity' => $txn->quantity,
                'notes' => $txn->notes,
                'user_name' => $txn->user->name,
            ];
        });

        return Inertia::render('Reports/Index', [
            'stats' => [
                'totalTransactions' => $totalTransactions,
                'totalMasuk' => (int) $totalMasuk,
                'totalKeluar' => (int) $totalKeluar,
            ],
            'transactions' => $transactions,
            'filters' => $request->only(['date_from', 'date_to', 'type']),
        ]);
    }
}
