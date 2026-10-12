@extends('layouts.app')

@section('title', 'Lacak Cucian - ' . $order->kode_order)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('tracking.index') }}" class="text-xs font-semibold text-brand-600 hover:underline flex items-center gap-1">
            ← Cek Resi Lainnya
        </a>
        <span class="text-xs text-slate-400">Status Update Realtime</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-6 p-6 sm:p-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Cucian</span>
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

        <!-- Progress Flow -->
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

        <!-- Detail Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 block">Layanan Paket:</span>
                <span class="font-semibold text-slate-800 text-sm">{{ $order->service->nama_layanan }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Berat / Qty:</span>
                <span class="font-semibold text-slate-800 text-sm">{{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Waktu Masuk:</span>
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
                <span class="text-slate-400 block">Total Tagihan:</span>
                <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Status Log -->
        <div class="space-y-3 pt-2">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Riwayat Status Cucian</h3>
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

</div>
@endsection
