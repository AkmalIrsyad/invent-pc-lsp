<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #ffffff;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: #ffffff;
            padding: 18px 24px;
            margin-bottom: 16px;
            border-radius: 6px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.85;
        }
        .header .meta {
            font-size: 9px;
            margin-top: 2px;
            opacity: 0.7;
        }

        /* ── Stats ───────────────────────────────────────────── */
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .stat-box {
            display: table-cell;
            width: 33%;
            background: #f0fafb;
            border: 1px solid #a5f3fc;
            border-left: 4px solid #0891b2;
            border-radius: 4px;
            padding: 10px 12px;
            text-align: center;
        }
        .stat-box.masuk  { border-left-color: #10b981; background: #f0fdf4; border-color: #a7f3d0; }
        .stat-box.keluar { border-left-color: #8b5cf6; background: #faf5ff; border-color: #ddd6fe; }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #0891b2;
        }
        .stat-box.masuk  .stat-value { color: #059669; }
        .stat-box.keluar .stat-value { color: #7c3aed; }
        .stat-label {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Filters info ────────────────────────────────────── */
        .filter-info {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        /* ── Table ───────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        thead tr {
            background: #0891b2;
            color: #ffffff;
        }
        thead th {
            padding: 7px 6px;
            text-align: left;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f0fafb; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td {
            padding: 6px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tbody td.center { text-align: center; }
        .badge-masuk  { color: #065f46; font-weight: bold; }
        .badge-keluar { color: #7f1d1d; font-weight: bold; }

        /* ── Footer ──────────────────────────────────────────── */
        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }

        /* ── Page break ──────────────────────────────────────── */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Sistem Informasi Inventaris PC — Invent-PC</div>
        <div class="meta">Digenerate pada: {{ now()->format('d M Y, H:i') }} WIB</div>
    </div>

    <!-- Stats -->
    <table class="stats">
        <tr>
            <td class="stat-box">
                <div class="stat-value">{{ $rows->count() }}</div>
                <div class="stat-label">Total Transaksi</div>
            </td>
            <td class="stat-box masuk">
                <div class="stat-value">{{ $rows->where('type','masuk')->sum('quantity') }}</div>
                <div class="stat-label">Total Barang Masuk</div>
            </td>
            <td class="stat-box keluar">
                <div class="stat-value">{{ $rows->where('type','keluar')->sum('quantity') }}</div>
                <div class="stat-label">Total Barang Keluar</div>
            </td>
        </tr>
    </table>

    <!-- Filter info -->
    @if(($filters['date_from'] ?? null) || ($filters['date_to'] ?? null) || ($filters['type'] ?? null))
    <div class="filter-info">
        Filter aktif:
        @if($filters['date_from'] ?? null) Dari: {{ $filters['date_from'] }} @endif
        @if($filters['date_to'] ?? null) s/d {{ $filters['date_to'] }} @endif
        @if($filters['type'] ?? null) | Tipe: {{ ucfirst($filters['type']) }} @endif
    </div>
    @endif

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width:28px">No</th>
                <th style="width:60px">Tanggal</th>
                <th style="width:52px">Kode</th>
                <th>Nama Barang</th>
                <th style="width:54px">Kategori</th>
                <th class="center" style="width:36px">Tipe</th>
                <th class="center" style="width:34px">Masuk</th>
                <th class="center" style="width:34px">Keluar</th>
                <th>Keterangan</th>
                <th style="width:60px">Pengguna</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $row['transaction_date'] }}</td>
                <td>{{ $row['product_code'] }}</td>
                <td>{{ $row['product_name'] }}</td>
                <td>{{ $row['category'] }}</td>
                <td class="center">
                    <span class="{{ $row['type'] === 'masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                        {{ ucfirst($row['type']) }}
                    </span>
                </td>
                <td class="center badge-masuk">
                    {{ $row['type'] === 'masuk' ? '+' . $row['quantity'] : '' }}
                </td>
                <td class="center badge-keluar">
                    {{ $row['type'] === 'keluar' ? '-' . $row['quantity'] : '' }}
                </td>
                <td>{{ $row['notes'] ?? '-' }}</td>
                <td>{{ $row['user_name'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:20px; color:#9ca3af;">
                    Tidak ada data transaksi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Invent-PC — Laporan Transaksi Inventaris &bull; {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
