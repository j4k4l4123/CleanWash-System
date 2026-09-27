@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->nama)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md bg-brand-100 text-brand-800 font-mono text-xs font-semibold">
                    {{ $customer->kode_pelanggan }}
                </span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $customer->nama }}</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Terdaftar sejak {{ $customer->created_at->isoFormat('D MMMM Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Order Cucian</span>
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Edit Data
            </a>
            <a href="{{ route('customers.index') }}" class="px-3.5 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Customer Info & Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Profile Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Informasi Kontak</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">No. Handphone / WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->no_hp) }}" target="_blank" class="font-mono font-medium text-brand-700 hover:underline flex items-center gap-1 mt-0.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.84 2.79.84h.001c3.181 0 5.768-2.586 5.768-5.766 0-3.18-2.587-5.766-5.768-5.766zm9.969 5.828c0 5.519-4.481 10-10 10-1.748 0-3.385-.45-4.817-1.238l-7.183 1.88 1.914-6.994c-.878-1.492-1.384-3.232-1.384-5.088 0-5.519 4.481-10 10-10s10 4.481 10 10z"/>
                        </svg>
                        <span>{{ $customer->no_hp }}</span>
                    </a>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat</span>
                    <p class="text-slate-700 mt-0.5">{{ $customer->alamat ?: 'Belum ada alamat' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Catatan Pelanggan</span>
                    <p class="text-slate-700 mt-0.5 italic">{{ $customer->catatan ?: '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Metric 1: Total Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Transaksi</span>
                <div class="text-3xl font-bold text-slate-900 mt-2">{{ $totalOrders }} <span class="text-sm font-normal text-slate-500">order</span></div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Cucian Aktif:</span>
                <span class="font-semibold text-amber-600">{{ $activeOrders }} cucian</span>
            </div>
        </div>

        <!-- Metric 2: Total Spent -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pengeluaran Lunas</span>
                <div class="text-3xl font-bold text-emerald-600 mt-2">Rp {{ number_format($totalSpending, 0, ',', '.') }}</div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Status:</span>
                <span class="font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Pelanggan Aktif</span>
            </div>
        </div>
    </div>

    <!-- Customer Orders History -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Riwayat Cucian {{ $customer->nama }}</h2>
            <p class="text-xs text-slate-500">Daftar transaksi cucian yang pernah dibuat oleh pelanggan ini.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Kode Order</th>
                        <th class="px-5 py-3.5">Tanggal Masuk</th>
                        <th class="px-5 py-3.5">Layanan</th>
                        <th class="px-5 py-3.5">Jumlah</th>
                        <th class="px-5 py-3.5">Total Biaya</th>
                        <th class="px-5 py-3.5">Status Cucian</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($customer->orders as $order)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-medium text-slate-900">
                                <a href="{{ route('orders.show', $order) }}" class="text-brand-600 hover:underline">
                                    {{ $order->kode_order }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $order->tgl_masuk->isoFormat('D MMM Y, HH:mm') }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $order->service->nama_layanan }}
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
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat pesanan cucian untuk pelanggan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
