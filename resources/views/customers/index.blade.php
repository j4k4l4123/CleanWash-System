@extends('layouts.app')

@section('title', 'Daftar Pelanggan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Pelanggan</h1>
            <p class="text-sm text-slate-500">Kelola informasi pelanggan, kontak, dan riwayat pesanan.</p>
        </div>
        <div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Box -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode pelanggan, no hp, atau alamat..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Customer Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Nama Pelanggan</th>
                        <th class="px-5 py-3.5">No. HP / WhatsApp</th>
                        <th class="px-5 py-3.5">Alamat</th>
                        <th class="px-5 py-3.5">Total Order</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($customers as $cust)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono text-xs font-semibold text-brand-700">
                                {{ $cust->kode_pelanggan }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('customers.show', $cust) }}" class="font-medium text-slate-900 hover:text-brand-600 transition">
                                    {{ $cust->nama }}
                                </a>
                                @if($cust->catatan)
                                    <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $cust->catatan }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">
                                {{ $cust->no_hp }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 max-w-xs truncate">
                                {{ $cust->alamat ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $cust->orders_count }} Transaksi
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('customers.show', $cust) }}" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('customers.edit', $cust) }}" class="px-2.5 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                                        Edit
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <form action="{{ route('customers.destroy', $cust) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ $cust->nama }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada data pelanggan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
