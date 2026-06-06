<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    /**
     * Build a base query with filters applied.
     */
    private function buildQuery(Request $request)
    {
        $query = Transaction::with(['product.category', 'user']);

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return $query;
    }

    /**
     * Format a transaction model into an array for display / export.
     */
    private function formatTransaction($txn): array
    {
        return [
            'id'               => $txn->id,
            'transaction_date' => $txn->transaction_date->format('d M Y'),
            'product_code'     => $txn->product->code,
            'product_name'     => $txn->product->name,
            'category'         => $txn->product->category->name ?? '-',
            'type'             => $txn->type,
            'quantity'         => $txn->quantity,
            'notes'            => $txn->notes,
            'user_name'        => $txn->user->name,
        ];
    }

    public function index(Request $request): Response
    {
        $query = $this->buildQuery($request);

        $allTransactions = (clone $query)->get();

        $totalTransactions = $allTransactions->count();
        $totalMasuk        = $allTransactions->where('type', 'masuk')->sum('quantity');
        $totalKeluar       = $allTransactions->where('type', 'keluar')->sum('quantity');

        $transactions = $query
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $transactions->getCollection()->transform(fn($txn) => $this->formatTransaction($txn));

        return Inertia::render('Reports/Index', [
            'stats' => [
                'totalTransactions' => $totalTransactions,
                'totalMasuk'        => (int) $totalMasuk,
                'totalKeluar'       => (int) $totalKeluar,
            ],
            'transactions' => $transactions,
            'filters'      => $request->only(['date_from', 'date_to', 'type']),
        ]);
    }

    public function export(Request $request, string $format)
    {
        $query        = $this->buildQuery($request);
        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc')->get();
        $rows         = $transactions->map(fn($txn) => $this->formatTransaction($txn));

        $filters = $request->only(['date_from', 'date_to', 'type']);
        $title   = 'Laporan Transaksi';
        if ($filters['date_from'] ?? null) {
            $title .= ' ' . $filters['date_from'];
        }
        if ($filters['date_to'] ?? null) {
            $title .= ' s/d ' . $filters['date_to'];
        }

        return match ($format) {
            'pdf'  => $this->exportPdf($rows, $title, $filters),
            'csv'  => $this->exportCsv($rows, $title),
            'xlsx' => $this->exportXlsx($rows, $title),
            default => abort(404),
        };
    }

    private function exportPdf($rows, string $title, array $filters)
    {
        $pdf = Pdf::loadView('reports.pdf', [
            'rows'    => $rows,
            'title'   => $title,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi.pdf');
    }

    private function exportCsv($rows, string $title)
    {
        $filename = 'laporan-transaksi.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $title) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");

            // Title row
            fputcsv($handle, [$title]);
            fputcsv($handle, ['Digenerate pada: ' . now()->format('d M Y H:i')]);
            fputcsv($handle, []);

            // Header row
            fputcsv($handle, ['No', 'Tanggal', 'Kode Barang', 'Nama Barang', 'Kategori', 'Tipe', 'Masuk', 'Keluar', 'Keterangan', 'Pengguna']);

            foreach ($rows as $i => $row) {
                fputcsv($handle, [
                    $i + 1,
                    $row['transaction_date'],
                    $row['product_code'],
                    $row['product_name'],
                    $row['category'],
                    ucfirst($row['type']),
                    $row['type'] === 'masuk'  ? $row['quantity'] : '',
                    $row['type'] === 'keluar' ? $row['quantity'] : '',
                    $row['notes'] ?? '-',
                    $row['user_name'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportXlsx($rows, string $title)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // ── Title ─────────────────────────────────────────────
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0e7490']],
        ]);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // ── Subtitle ──────────────────────────────────────────
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Digenerate pada: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e0f2f7']],
        ]);

        // ── Column headers ────────────────────────────────────
        $headers = ['No', 'Tanggal', 'Kode Barang', 'Nama Barang', 'Kategori', 'Tipe', 'Masuk', 'Keluar', 'Keterangan', 'Pengguna'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet->getStyle('A3:J3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0891b2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Data rows ─────────────────────────────────────────
        foreach ($rows as $i => $row) {
            $rowNum = $i + 4;
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $row['transaction_date']);
            $sheet->setCellValue('C' . $rowNum, $row['product_code']);
            $sheet->setCellValue('D' . $rowNum, $row['product_name']);
            $sheet->setCellValue('E' . $rowNum, $row['category']);
            $sheet->setCellValue('F' . $rowNum, ucfirst($row['type']));
            $sheet->setCellValue('G' . $rowNum, $row['type'] === 'masuk'  ? $row['quantity'] : '');
            $sheet->setCellValue('H' . $rowNum, $row['type'] === 'keluar' ? $row['quantity'] : '');
            $sheet->setCellValue('I' . $rowNum, $row['notes'] ?? '-');
            $sheet->setCellValue('J' . $rowNum, $row['user_name']);

            // Zebra stripes
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:J{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FAFB']],
                ]);
            }

            // Color Masuk/Keluar cells
            if ($row['type'] === 'masuk') {
                $sheet->getStyle("G{$rowNum}")->getFont()->getColor()->setRGB('15803d');
            } else {
                $sheet->getStyle("H{$rowNum}")->getFont()->getColor()->setRGB('b91c1c');
            }

            $sheet->getStyle("A{$rowNum}:J{$rowNum}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
        }

        // ── Column widths ─────────────────────────────────────
        $widths = ['A' => 5, 'B' => 14, 'C' => 12, 'D' => 28, 'E' => 16, 'F' => 10, 'G' => 8, 'H' => 8, 'I' => 30, 'J' => 18];
        foreach ($widths as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // ── Output ────────────────────────────────────────────
        $filename = 'laporan-transaksi.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
