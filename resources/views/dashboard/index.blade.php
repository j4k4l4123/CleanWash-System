@extends('layouts.app')

@section('title', 'Dashboard Ringkasan')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Laundry</h1>
            <p class="text-sm text-slate-500">Ringkasan operasional dan aktivitas cucian hari ini, {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('tracking.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Cek Resi</span>
            </a>
            <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Terima Cucian Baru</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue Today -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Pendapatan Hari Ini</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($revenueToday, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-500 mt-1">Bulan ini: Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Orders Today -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Order Masuk Hari Ini</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $ordersTodayCount }} <span class="text-sm font-normal text-slate-500">order</span></div>
                <div class="text-xs text-slate-500 mt-1">Total bulan ini: {{ $ordersThisMonthCount }} order</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
        </div>

        <!-- Active Orders Processing -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Sedang Diproses</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $activeOrdersCount }} <span class="text-sm font-normal text-slate-500">cucian</span></div>
                <div class="text-xs text-slate-500 mt-1">{{ $readyForPickupCount }} sudah siap diambil</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
        </div>

        <!-- Unpaid Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Belum Lunas</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ $unpaidCount }} <span class="text-sm font-normal text-slate-500">tagihan</span></div>
                <div class="text-xs text-slate-500 mt-1">{{ $totalCustomers }} pelanggan terdaftar</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Status Flow Progress Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <h2 class="text-sm font-semibold text-slate-900 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Status Antrean Cucian Saat Ini</span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ route('orders.index', ['status_cucian' => 'diterima']) }}" class="p-4 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-100/50 transition">
                <div class="text-xs font-medium text-amber-800">1. Diterima</div>
                <div class="text-2xl font-bold text-amber-900 mt-1">{{ $statusCounts['diterima'] }}</div>
            </a>
            <a href="{{ route('orders.index', ['status_cucian' => 'siap_diambil']) }}" class="p-4 rounded-xl border border-teal-200 bg-teal-50/50 hover:bg-teal-100/50 transition">
                <div class="text-xs font-medium text-teal-800">2. Siap Diambil</div>
                <div class="text-2xl font-bold text-teal-900 mt-1">{{ $statusCounts['siap_diambil'] }}</div>
            </a>
            <a href="{{ route('orders.index', ['status_cucian' => 'selesai']) }}" class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100/50 transition">
                <div class="text-xs font-medium text-emerald-800">3. Selesai</div>
                <div class="text-2xl font-bold text-emerald-900 mt-1">{{ $statusCounts['selesai'] }}</div>
            </a>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Aktivitas Cucian Terbaru</h2>
                <p class="text-xs text-slate-500">Pantau transaksi dan status pengerjaan cucian terkini.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                <span>Lihat Semua</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Kode Order</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Layanan</th>
                        <th class="px-5 py-3.5">Jumlah / Berat</th>
                        <th class="px-5 py-3.5">Total Biaya</th>
                        <th class="px-5 py-3.5">Status Cucian</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentOrders as $order)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-medium text-slate-900">
                                <a href="{{ route('orders.show', $order) }}" class="text-brand-600 hover:underline">
                                    {{ $order->kode_order }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-sans mt-0.5">{{ $order->tgl_masuk->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">{{ $order->customer->nama }}</div>
                                <div class="text-xs text-slate-400">{{ $order->customer->no_hp }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-800">{{ $order->service->nama_layanan }}</span>
                            </td>
                            <td class="px-5 py-4">
                                {{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-900">
                                Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $order->payment_badge_class }}">
                                    {{ $order->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                Belum ada order cucian. Silakan buat order baru!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
