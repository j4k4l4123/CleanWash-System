@extends('layouts.app')

@section('title', 'Lacak Status Cucian Laundry')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Search Hero Box -->
    <div class="bg-gradient-to-br from-brand-600 to-teal-800 rounded-3xl p-6 sm:p-10 text-white shadow-md text-center space-y-4">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-xs text-white mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <div class="max-w-md mx-auto space-y-1">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Lacak Status Cucian Anda</h1>
            <p class="text-teal-100 text-xs sm:text-sm">Masukkan Kode Order (Resi) atau Nomor HP yang terdaftar saat mencuci.</p>
        </div>

        <form method="GET" action="{{ route('tracking.index') }}" class="max-w-md mx-auto flex gap-2 pt-2">
            <input type="text" name="q" value="{{ $search }}" required placeholder="Contoh: ORD-20260918-001 atau 081234567890" class="flex-1 px-4 py-3 text-sm text-slate-800 bg-white rounded-2xl shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-300">
            <button type="submit" class="px-5 py-3 text-sm font-semibold bg-slate-900 text-white rounded-2xl hover:bg-slate-800 transition shadow-xs">
                Cek Resi
            </button>
        </form>
    </div>

    <!-- Tracking Result -->
    @if($search && !$order)
        <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xs text-center space-y-2">
            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-800">Order Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Nomor resi atau no HP "<span class="font-mono font-medium">{{ $search }}</span>" tidak ditemukan dalam sistem kami. Harap pastikan kembali kode yang dimasukkan sudah benar.
            </p>
        </div>
    @elseif($order)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-6 p-6 sm:p-8">
            
            <!-- Result Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hasil Pelacakan Cucian</span>
                    <div class="flex items-center gap-2.5 mt-1">
                        <span class="text-xl font-bold font-mono text-slate-900">{{ $order->kode_order }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-400 block">Pelanggan:</span>
                    <span class="text-sm font-semibold text-slate-800">{{ $order->customer->nama }}</span>
                </div>
            </div>

            <!-- Progress Timeline -->
            @php
                $flowSteps = [
                    'diterima' => '1. Diterima',
                    'siap_diambil' => '2. Siap Diambil',
                    'selesai' => '3. Selesai',
                ];
                $stepKeys = array_keys($flowSteps);
                $currentIndex = array_search($order->status_cucian, $stepKeys);
            @endphp

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Progres Pengerjaan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                    @foreach ($flowSteps as $stepKey => $stepTitle)
                        @php
                            $stepIndex = array_search($stepKey, $stepKeys);
                            $isPassed = $currentIndex !== false && $stepIndex <= $currentIndex;
                            $isCurrent = $stepKey === $order->status_cucian;
                        @endphp
                        <div class="p-3.5 rounded-xl border transition {{ $isCurrent ? 'border-brand-500 bg-brand-50/80 text-brand-900 font-bold shadow-xs' : ($isPassed ? 'border-emerald-200 bg-emerald-50/40 text-emerald-800' : 'border-slate-100 bg-slate-50 text-slate-400') }}">
                            <div class="flex items-center justify-center mb-1.5">
                                @if($isPassed)
                                    <svg class="w-5 h-5 {{ $isCurrent ? 'text-brand-600' : 'text-emerald-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                                @endif
                            </div>
                            <div class="text-xs sm:text-sm font-medium">{{ $stepTitle }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 block">Layanan:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $order->service->nama_layanan }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Berat / Jumlah:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Waktu Diterima:</span>
                    <span class="font-medium text-slate-700">{{ $order->tgl_masuk->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Estimasi Selesai:</span>
                    <span class="font-medium text-slate-700">{{ $order->estimasi_selesai ? $order->estimasi_selesai->isoFormat('D MMMM Y, HH:mm') . ' WIB' : '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Status Pembayaran:</span>
                    <span class="font-bold uppercase {{ $order->status_pembayaran === 'lunas' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $order->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Total Biaya:</span>
                    <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Status Log Timeline -->
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Riwayat Perjalanan Cucian</h3>
                <div class="space-y-2.5">
                    @foreach ($order->statusLogs as $log)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-1.5 h-1.5 rounded-full bg-brand-600 mt-1.5"></div>
                            <div class="flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between text-slate-700">
                                <span class="font-medium">{{ $log->keterangan }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
