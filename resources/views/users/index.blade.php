@extends('layouts.app')

@section('title', 'Kelola Petugas & Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kelola Petugas & Pengguna</h1>
            <p class="text-sm text-slate-500">Atur hak akses akun administrator dan kasir laundry.</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Petugas</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Box -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, email, atau nomor HP..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="w-full sm:w-48">
                <select name="role" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Peran (Role)</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin / Owner</option>
                    <option value="kasir" {{ $role === 'kasir' ? 'selected' : '' }}>Kasir / Petugas</option>
                    <option value="pekerja" {{ $role === 'pekerja' ? 'selected' : '' }}>Pekerja Laundry</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Filter
            </button>

            @if($search || $role)
                <a href="{{ route('users.index') }}" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Nama Petugas</th>
                        <th class="px-5 py-3.5">Email Akun</th>
                        <th class="px-5 py-3.5">Role / Peran</th>
                        <th class="px-5 py-3.5">No. HP</th>
                        <th class="px-5 py-3.5">Terdaftar Sejak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($users as $u)
                        <tr onclick="if(!event.target.closest('button, select, input, a')) window.location='{{ route('users.edit', $u) }}'" class="hover:bg-slate-50/80 transition cursor-pointer group">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 group-hover:text-brand-600 transition flex items-center gap-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if($u->id === Auth::id())
                                                <span class="text-[10px] font-medium bg-brand-50 text-brand-700 border border-brand-200 px-1.5 py-0.2 rounded-md">Akun Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">
                                {{ $u->email }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $u->role_badge_class }}">
                                    {{ $u->role_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $u->no_hp ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 text-xs">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data petugas yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
