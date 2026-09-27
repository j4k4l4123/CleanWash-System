@extends('layouts.app')

@section('title', 'Manajemen Pembayaran & Kas')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Pembayaran</h1>
            <p class="text-sm text-slate-500">Pencatatan kas masuk, invoice, dan rekap metode transaksi.</p>
        </div>
    </div>

    <!-- Revenue Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pemasukan Lunas</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Akumulasi seluruh transaksi</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penerimaan Tunai (Cash)</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Uang fisik di kasir</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penerimaan QRIS</span>
            <div class="text-2xl font-bold text-teal-600 mt-1">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">E-Wallet & QRIS static</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penerimaan Transfer</span>
            <div class="text-2xl font-bold text-blue-600 mt-1">Rp {{ number_format($transferRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Bank transfer BCA/BRI/Mandiri</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice, order, nama..." class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition">
            </div>

            <div>
                <select name="metode_pembayaran" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition">
                    <option value="">Semua Metode Pembayaran</option>
                    <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ request('metode_pembayaran') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ request('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition" title="Dari tanggal">
                <span class="text-slate-400 text-xs">-</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition" title="Sampai tanggal">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'metode_pembayaran', 'date_from', 'date_to']))
                    <a href="{{ route('payments.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">No. Invoice</th>
                        <th class="px-5 py-3.5">Tanggal Bayar</th>
                        <th class="px-5 py-3.5">Kode Order</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Metode</th>
                        <th class="px-5 py-3.5">Jumlah Bayar</th>
                        <th class="px-5 py-3.5">Uang Diterima / Kembalian</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($payments as $pay)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $pay->kode_pembayaran }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $pay->tgl_bayar->isoFormat('D MMM Y, HH:mm') }}
                            </td>
                            <td class="px-5 py-4 font-mono text-xs">
                                <a href="{{ route('orders.show', $pay->order) }}" class="text-brand-600 hover:underline">
                                    {{ $pay->order->kode_order }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">{{ $pay->order->customer->nama }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $pay->order->customer->no_hp }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $pay->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-emerald-600">
                                Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                @if($pay->metode_pembayaran === 'tunai' && $pay->uang_diterima)
                                    <div>Masuk: Rp {{ number_format($pay->uang_diterima, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-slate-400">Kembalian: Rp {{ number_format($pay->kembalian, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-slate-400">Sesuai Tagihan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('payments.receipt', $pay) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition">
                                    Kwitansi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat pembayaran yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
