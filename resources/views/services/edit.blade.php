@extends('layouts.app')

@section('title', 'Edit Layanan - ' . $service->nama_layanan)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Layanan Laundry</h1>
            <p class="text-sm text-slate-500">Perbarui tarif atau durasi pengerjaan.</p>
        </div>
        <a href="{{ route('services.index') }}" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('services.update', $service) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nama_layanan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Layanan / Paket <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="nama_layanan" name="nama_layanan" value="{{ old('nama_layanan', $service->nama_layanan) }}" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jenis" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Perhitungan <span class="text-rose-500">*</span>
                    </label>
                    <select id="jenis" name="jenis" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="kiloan" {{ old('jenis', $service->jenis) == 'kiloan' ? 'selected' : '' }}>Kiloan (Per Kg)</option>
                        <option value="satuan" {{ old('jenis', $service->jenis) == 'satuan' ? 'selected' : '' }}>Satuan (Per Pcs/Item)</option>
                    </select>
                </div>

                <div>
                    <label for="harga" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Harga (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="harga" name="harga" value="{{ old('harga', $service->harga) }}" required min="0" step="500" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label for="durasi_jam" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Estimasi Durasi Pengerjaan (Jam) <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="durasi_jam" name="durasi_jam" value="{{ old('durasi_jam', $service->durasi_jam) }}" required min="1" class="w-36 px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <span class="text-xs text-slate-500">Jam (Contoh: 6 jam untuk express, 24 jam untuk 1 hari, 48 jam untuk 2 hari)</span>
                </div>
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Deskripsi / Keterangan Layanan
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="2" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('deskripsi', $service->deskripsi) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                <label for="is_active" class="text-sm font-medium text-slate-700">Layanan Aktif</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('services.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    Perbarui Layanan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
