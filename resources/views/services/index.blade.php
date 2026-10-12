@extends('layouts.app')

@section('title', 'Daftar Layanan & Paket Laundry')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Layanan & Paket Laundry</h1>
            <p class="text-sm text-slate-500">Kelola daftar harga kiloan, satuan, dan estimasi durasi pengerjaan.</p>
        </div>
        @if(Auth::user()->isAdmin())
            <div>
                <a href="{{ route('services.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Layanan</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Nama Layanan</th>
                        <th class="px-5 py-3.5">Jenis Paket</th>
                        <th class="px-5 py-3.5">Harga Satuan</th>
                        <th class="px-5 py-3.5">Estimasi Durasi</th>
                        <th class="px-5 py-3.5">Total Digunakan</th>
                        <th class="px-5 py-3.5">Status</th>
                        @if(Auth::user()->isAdmin())
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $service->nama_layanan }}</div>
                                @if($service->deskripsi)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $service->deskripsi }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($service->jenis === 'kiloan')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        Per Kilogram (Kg)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        Per Satuan (Pcs)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                Rp {{ number_format($service->harga, 0, ',', '.') }}
                                <span class="text-xs font-normal text-slate-500">/ {{ $service->jenis === 'kiloan' ? 'kg' : 'pcs' }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $service->durasi_jam }} Jam ({{ round($service->durasi_jam / 24, 1) }} hari)</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs font-medium text-slate-600">
                                {{ $service->orders_count }} Transaksi
                            </td>
                            <td class="px-5 py-4">
                                @if($service->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Non-aktif
                                    </span>
                                @endif
                            </td>
                            @if(Auth::user()->isAdmin())
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('services.toggle', $service) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Ubah status aktif">
                                                {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('services.edit', $service) }}" class="px-2.5 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                                            Edit
                                        </a>
                                        @if($service->orders_count === 0)
                                            <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Hapus layanan ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Belum ada layanan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
