@extends('layouts.app')

@section('title', 'Daftar Order Cucian')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Order & Transaksi Cucian</h1>
            <p class="text-sm text-slate-500">Kelola dan pantau seluruh transaksi penerimaan cucian.</p>
        </div>
        <div>
            <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Terima Order Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode order, nama pelanggan, no hp..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Status Cucian -->
            <div>
                <select name="status_cucian" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Status Cucian</option>
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status_cucian') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Pembayaran -->
            <div>
                <select name="status_pembayaran" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Pembayaran</option>
                    <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition text-center">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status_cucian', 'status_pembayaran', 'date_from', 'date_to']))
                    <a href="{{ route('orders.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Kode Order</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Layanan</th>
                        <th class="px-5 py-3.5">Berat / Qty</th>
                        <th class="px-5 py-3.5">Total Biaya</th>
                        <th class="px-5 py-3.5">Status Cucian</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-semibold text-slate-900">
                                <a href="{{ route('orders.show', $order) }}" class="text-brand-600 hover:underline">
                                    {{ $order->kode_order }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                <div>{{ $order->tgl_masuk->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->tgl_masuk->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">{{ $order->customer->nama }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $order->customer->no_hp }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-800">{{ $order->service->nama_layanan }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                {{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
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
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('orders.show', $order) }}" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="px-2.5 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition" title="Cetak Nota">
                                        Nota
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data order cucian yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
