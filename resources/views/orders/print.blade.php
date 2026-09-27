<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Cucian {{ $order->kode_order }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="alternate icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-receipt { font-family: 'Courier Prime', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-container { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <!-- Action Toolbar (Hidden in Print) -->
    <div class="max-w-md w-full mb-4 flex items-center justify-between no-print">
        <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            ← Kembali ke Order
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-xs">
            Cetak Nota (Print)
        </button>
    </div>

    <!-- Printable Receipt Container -->
    <div class="print-container max-w-sm w-full bg-white p-6 rounded-2xl border border-slate-200 shadow-sm font-receipt text-xs text-slate-800 space-y-4">
        
        <!-- Header -->
        <div class="text-center space-y-1 pb-3 border-b border-dashed border-slate-300">
            <h1 class="text-base font-bold tracking-wider font-sans text-slate-900 uppercase">CLEANWASH LAUNDRY</h1>
            <p class="text-[11px] text-slate-500 font-sans">Jl. Kampus No. 42, Telp/WA: 0812-3456-7890</p>
            <p class="text-[10px] text-slate-400 font-sans">Kelompok 2 - Layanan Laundry Bersih & Cepat</p>
        </div>

        <!-- Order Meta -->
        <div class="space-y-1 pb-3 border-b border-dashed border-slate-300">
            <div class="flex justify-between">
                <span>No. Order:</span>
                <span class="font-bold font-sans">{{ $order->kode_order }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tgl Masuk:</span>
                <span>{{ $order->tgl_masuk->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Estimasi Selesai:</span>
                <span>{{ $order->estimasi_selesai ? $order->estimasi_selesai->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="font-bold">{{ $order->customer->nama }}</span>
            </div>
            <div class="flex justify-between">
                <span>No. HP:</span>
                <span>{{ $order->customer->no_hp }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="space-y-2 pb-3 border-b border-dashed border-slate-300">
            <div class="flex justify-between font-bold">
                <span>Layanan</span>
                <span>Total</span>
            </div>
            <div>
                <div class="font-bold text-slate-900">{{ $order->service->nama_layanan }}</div>
                <div class="flex justify-between text-[11px] text-slate-600">
                    <span>{{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }} x Rp {{ number_format($order->harga_per_satuan, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>
            @if($order->catatan)
                <div class="text-[11px] text-slate-500 italic mt-1">
                    * Catatan: {{ $order->catatan }}
                </div>
            @endif
        </div>

        <!-- Total & Payment Details -->
        <div class="space-y-1.5 pb-3 border-b border-dashed border-slate-300">
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Status Bayar:</span>
                <span class="font-bold uppercase {{ $order->status_pembayaran === 'lunas' ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $order->status_pembayaran === 'lunas' ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
            </div>

            @if($order->payment)
                <div class="flex justify-between">
                    <span>Metode:</span>
                    <span class="uppercase">{{ $order->payment->metode_pembayaran }}</span>
                </div>
                @if($order->payment->metode_pembayaran === 'tunai')
                    <div class="flex justify-between text-slate-600">
                        <span>Uang Diterima:</span>
                        <span>Rp {{ number_format($order->payment->uang_diterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($order->payment->kembalian, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-[10px] text-slate-400">
                    <span>No. Invoice:</span>
                    <span>{{ $order->payment->kode_pembayaran }}</span>
                </div>
            @endif
        </div>

        <!-- Tracking Instructions -->
        <div class="text-center space-y-1 text-[11px] text-slate-500 font-sans">
            <p class="font-medium text-slate-700">Lacak Cucian Online:</p>
            <p class="font-mono text-[10px] text-teal-700 font-semibold">{{ url('/tracking/' . $order->kode_order) }}</p>
            <p class="text-[10px] text-slate-400 pt-1">
                Harap tunjukkan nota ini saat pengambilan pakaian.<br>
                Komplain maksimal 1x24 jam setelah cucian diambil.
            </p>
            <p class="font-semibold text-slate-800 pt-2 font-sans">Terima Kasih Atas Kepercayaan Anda!</p>
        </div>

    </div>

    <script>
        // Optional auto-print if URL has ?autoprint=1
        if (window.location.search.includes('autoprint=1')) {
            window.print();
        }
    </script>
</body>
</html>
