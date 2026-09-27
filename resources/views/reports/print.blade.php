<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Operasional Laundry ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="alternate icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center">

    <!-- Print Action Bar -->
    <div class="max-w-4xl w-full mb-4 flex items-center justify-between no-print">
        <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Kembali ke Menu Laporan
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-xs">
            Cetak Dokumen Laporan (Print / Save as PDF)
        </button>
    </div>

    <div class="max-w-4xl w-full bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-sm text-slate-800 space-y-6">
        
        <!-- Document Header -->
        <div class="border-b-2 border-slate-900 pb-4 flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 uppercase">CLEANWASH MANAGEMENT LAUNDRY</h1>
                <p class="text-xs text-slate-500">Laporan Keuangan & Rekapitulasi Operasional Cucian</p>
                <p class="text-xs text-slate-400">Proyek Sistem Informasi Laundry - Kelompok 2</p>
            </div>
            <div class="text-right text-xs">
                <span class="text-slate-400 block">Periode Laporan:</span>
                <span class="font-bold text-slate-900">{{ $startDate->isoFormat('D MMMM Y') }} - {{ $endDate->isoFormat('D MMMM Y') }}</span>
                <div class="text-[11px] text-slate-400 mt-1">Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</div>
            </div>
        </div>

        <!-- Metric Summary in Document -->
        <div class="grid grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Total Omzet Lunas</span>
                <span class="text-base font-bold text-emerald-700 mt-0.5 block">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Total Piutang</span>
                <span class="text-base font-bold text-rose-700 mt-0.5 block">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Total Order</span>
                <span class="text-base font-bold text-slate-900 mt-0.5 block">{{ $totalOrders }} Transaksi</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Volume Kiloan</span>
                <span class="text-base font-bold text-slate-900 mt-0.5 block">{{ number_format($totalKiloan, 1) }} Kg</span>
            </div>
        </div>

        <!-- Payment Breakdown Mini Table -->
        <div class="text-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] mb-2">Rincian Kas Masuk per Metode Pembayaran</h3>
            <div class="grid grid-cols-3 gap-3 border border-slate-200 rounded-xl p-3 bg-slate-50 text-center">
                <div>
                    <span class="text-slate-500 block">Uang Tunai:</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($methodBreakdown['tunai'], 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">QRIS:</span>
                    <span class="font-bold text-teal-700">Rp {{ number_format($methodBreakdown['qris'], 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Transfer Bank:</span>
                    <span class="font-bold text-blue-700">Rp {{ number_format($methodBreakdown['transfer'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="space-y-2">
            <h3 class="font-bold text-slate-900 uppercase text-[11px]">Daftar Rincian Transaksi Order</h3>
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-2.5">No. Order</th>
                        <th class="p-2.5">Tanggal</th>
                        <th class="p-2.5">Pelanggan</th>
                        <th class="p-2.5">Layanan</th>
                        <th class="p-2.5">Berat / Qty</th>
                        <th class="p-2.5">Status Cucian</th>
                        <th class="p-2.5">Status Bayar</th>
                        <th class="p-2.5 text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="p-2.5 font-mono font-medium">{{ $order->kode_order }}</td>
                            <td class="p-2.5 text-slate-500">{{ $order->tgl_masuk->format('d/m/Y') }}</td>
                            <td class="p-2.5 font-medium">{{ $order->customer->nama }}</td>
                            <td class="p-2.5">{{ $order->service->nama_layanan }}</td>
                            <td class="p-2.5">{{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}</td>
                            <td class="p-2.5 uppercase font-medium">{{ $order->status_label }}</td>
                            <td class="p-2.5 uppercase font-bold {{ $order->status_pembayaran === 'lunas' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $order->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                            </td>
                            <td class="p-2.5 text-right font-semibold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-slate-400">Tidak ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="pt-8 flex items-center justify-between text-xs text-slate-600 text-center">
            <div>
                <p>Mengetahui, Penanggung Jawab</p>
                <div class="h-14"></div>
                <p class="font-bold text-slate-800">( Ketua Kelompok 2 )</p>
            </div>
            <div>
                <p>Kasir / Admin Operasional</p>
                <div class="h-14"></div>
                <p class="font-bold text-slate-800">( {{ auth()->user()->name ?? 'Administrator' }} )</p>
            </div>
        </div>

    </div>

</body>
</html>
