@extends('layouts.app')

@section('title', 'Laporan Keuangan & Operasional Laundry')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan & Rekapitulasi</h1>
            <p class="text-sm text-slate-500">
                Periode: {{ $startDate->isoFormat('D MMMM Y') }} s/d {{ $endDate->isoFormat('D MMMM Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.print', request()->all()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Period Preset -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Periode Waktu</label>
                <select name="period" id="periodSelect" onchange="toggleCustomDates()" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="this_week" {{ $filter === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ $filter === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="last_month" {{ $filter === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                    <option value="custom" {{ $filter === 'custom' ? 'selected' : '' }}>Rentang Tanggal Custom</option>
                </select>
            </div>

            <!-- Custom Dates -->
            <div id="customDateRow" class="{{ $filter === 'custom' ? '' : 'hidden' }} sm:col-span-2 grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Mulai Dari</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Sampai Dengan</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Filter Status Bayar -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                <select name="status_pembayaran" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    <option value="">Semua Status</option>
                    <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                </select>
            </div>

            <!-- Filter Layanan -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Paket Layanan</label>
                <select name="service_id" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    <option value="">Semua Layanan</option>
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->nama_layanan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition">
                    Terapkan
                </button>
                <a href="{{ route('reports.index') }}" class="px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Omzet -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omzet Lunas</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Uang masuk berhasil ditagih</div>
        </div>

        <!-- Total Piutang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Piutang Belum Lunas</span>
            <div class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tagihan kasir tertunda</div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Order Masuk</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalOrders }} <span class="text-xs font-normal text-slate-500">transaksi</span></div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $totalSelesai }} cucian selesai</div>
        </div>

        <!-- Cucian Sedang Proses -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sedang Dikerjakan</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $totalProses }} <span class="text-xs font-normal text-slate-500">order</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Diterima & siap diambil</div>
        </div>

        <!-- Total Berat Kg -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Berat Kiloan</span>
            <div class="text-2xl font-bold text-brand-700 mt-1">{{ number_format($totalKiloan, 1) }} <span class="text-xs font-normal text-slate-500">Kg</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Volume cucian kiloan</div>
        </div>
    </div>

    <!-- Revenue Breakdown by Payment Method -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase">Kas Tunai (Cash)</span>
                <div class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($methodBreakdown['tunai'], 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                CASH
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase">Penerimaan QRIS</span>
                <div class="text-xl font-bold text-teal-600 mt-1">Rp {{ number_format($methodBreakdown['qris'], 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xs">
                QRIS
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase">Transfer Bank</span>
                <div class="text-xl font-bold text-blue-600 mt-1">Rp {{ number_format($methodBreakdown['transfer'], 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                BANK
            </div>
        </div>
    </div>

    <!-- Orders Detail Table in Period -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Rincian Transaksi Periode Terpilih</h2>
                <p class="text-xs text-slate-500">Menampilkan {{ $orders->count() }} transaksi laundry.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Kode Order</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Layanan</th>
                        <th class="px-5 py-3.5">Berat / Qty</th>
                        <th class="px-5 py-3.5">Total Tagihan</th>
                        <th class="px-5 py-3.5">Status Cucian</th>
                        <th class="px-5 py-3.5">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 font-mono font-medium text-slate-900">
                                <a href="{{ route('orders.show', $order) }}" class="text-brand-600 hover:underline">
                                    {{ $order->kode_order }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $order->tgl_masuk->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                {{ $order->customer->nama }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-800">
                                {{ $order->service->nama_layanan }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kg' : 'Pcs' }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $order->payment_badge_class }}">
                                    {{ $order->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada transaksi pada rentang waktu ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function toggleCustomDates() {
        const period = document.getElementById('periodSelect').value;
        const customBox = document.getElementById('customDateRow');
        if (period === 'custom') {
            customBox.classList.remove('hidden');
        } else {
            customBox.classList.add('hidden');
        }
    }
</script>
@endpush

@endsection
