@extends('layouts.app')

@section('title', 'Edit Order ' . $order->kode_order)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Data Order</h1>
            <p class="text-sm text-slate-500">Perbarui rincian {{ $order->kode_order }}.</p>
        </div>
        <a href="{{ route('orders.show', $order) }}" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="customer_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Pelanggan <span class="text-rose-500">*</span>
                </label>
                <select id="customer_id" name="customer_id" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}" {{ old('customer_id', $order->customer_id) == $cust->id ? 'selected' : '' }}>
                            {{ $cust->nama }} ({{ $cust->kode_pelanggan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="service_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Layanan / Paket <span class="text-rose-500">*</span>
                </label>
                <select id="service_id" name="service_id" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    @foreach ($services as $svc)
                        <option value="{{ $svc->id }}" {{ old('service_id', $order->service_id) == $svc->id ? 'selected' : '' }}>
                            {{ $svc->nama_layanan }} - Rp {{ number_format($svc->harga, 0, ',', '.') }}/{{ $svc->jenis === 'kiloan' ? 'kg' : 'pcs' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="berat_atau_jumlah" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Berat (Kg) / Jumlah (Pcs) <span class="text-rose-500">*</span>
                </label>
                <input type="number" step="0.1" min="0.1" id="berat_atau_jumlah" name="berat_atau_jumlah" value="{{ old('berat_atau_jumlah', $order->berat_atau_jumlah) }}" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Khusus
                </label>
                <textarea id="catatan" name="catatan" rows="3" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">{{ old('catatan', $order->catatan) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
