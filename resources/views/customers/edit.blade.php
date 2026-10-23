@extends('layouts.app')

@section('title', 'Edit Pelanggan - ' . $customer->nama)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Data Pelanggan</h1>
            <p class="text-sm text-slate-500">Perbarui informasi {{ $customer->nama }} ({{ $customer->kode_pelanggan }}).</p>
        </div>

    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $customer->nama) }}" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label for="no_hp" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    No. Handphone / WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $customer->no_hp) }}" required class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label for="alamat" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Lengkap
                </label>
                <textarea id="alamat" name="alamat" rows="3" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('alamat', $customer->alamat) }}</textarea>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Khusus
                </label>
                <input type="text" id="catatan" name="catatan" value="{{ old('catatan', $customer->catatan) }}" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    @if(Auth::user()->isAdmin())
                        <button type="submit" form="delete-customer-form" class="px-4 py-2 text-sm font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                            Hapus Pelanggan
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                        Perbarui Pelanggan
                    </button>
                </div>
            </div>
        </form>

        @if(Auth::user()->isAdmin())
            <form id="delete-customer-form" action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ $customer->nama }}?');" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

</div>
@endsection
