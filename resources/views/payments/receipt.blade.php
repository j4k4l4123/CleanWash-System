<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran {{ $payment->kode_pembayaran }}</title>
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
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <div class="max-w-md w-full mb-4 flex items-center justify-between no-print">
        <a href="{{ route('payments.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Kembali ke Pembayaran
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-xs">
            Cetak Kwitansi
        </button>
    </div>

    <div class="max-w-md w-full bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm text-sm text-slate-800 space-y-6">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900">CLEANWASH LAUNDRY</h1>
                <p class="text-xs text-slate-500">Bukti Pembayaran Resmi (Kwitansi)</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">
                LUNAS
            </span>
        </div>

        <!-- Meta info -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block">No. Invoice:</span>
                <span class="font-mono font-bold text-slate-900">{{ $payment->kode_pembayaran }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Tanggal Bayar:</span>
                <span class="font-medium text-slate-800">{{ $payment->tgl_bayar->format('d F Y, H:i') }} WIB</span>
            </div>
            <div>
                <span class="text-slate-400 block">No. Order Cucian:</span>
                <span class="font-mono font-semibold text-slate-900">{{ $payment->order->kode_order }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Metode Pembayaran:</span>
                <span class="font-semibold uppercase text-slate-800">{{ $payment->metode_pembayaran }}</span>
            </div>
        </div>

        <!-- Received from -->
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs space-y-1">
            <span class="text-slate-400 block">Telah Diterima Dari:</span>
            <div class="font-bold text-slate-900 text-sm">{{ $payment->order->customer->nama }}</div>
            <div class="text-slate-500">{{ $payment->order->customer->no_hp }}</div>
        </div>

        <!-- Details -->
        <div class="border-t border-b border-slate-100 py-3 space-y-2 text-xs">
            <div class="flex justify-between font-semibold text-slate-500 uppercase text-[10px]">
                <span>Deskripsi Layanan</span>
                <span>Subtotal</span>
            </div>
            <div class="flex justify-between">
                <span>{{ $payment->order->service->nama_layanan }} ({{ $payment->order->berat_atau_jumlah }} {{ $payment->order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }})</span>
                <span class="font-semibold">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            @if($payment->metode_pembayaran === 'tunai' && $payment->uang_diterima)
                <div class="flex justify-between text-slate-500 pt-2 border-t border-slate-100">
                    <span>Uang Diterima Tunai:</span>
                    <span>Rp {{ number_format($payment->uang_diterima, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Kembalian:</span>
                    <span>Rp {{ number_format($payment->kembalian, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Grand Total -->
        <div class="flex items-center justify-between text-base font-bold text-slate-900">
            <span>Total Pembayaran:</span>
            <span class="text-xl text-emerald-600">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</span>
        </div>

        <!-- Footer signatures -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 text-center">
            <div>
                <p>Pelanggan,</p>
                <div class="h-12"></div>
                <p class="font-medium text-slate-800">({{ $payment->order->customer->nama }})</p>
            </div>
            <div>
                <p>Kasir / Pengelola,</p>
                <div class="h-12"></div>
                <p class="font-medium text-slate-800">(CleanWash Laundry)</p>
            </div>
        </div>

    </div>

</body>
</html>
