@extends('layouts.app')

@section('title', 'Tambah Petugas Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 transition mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Daftar Petugas</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Petugas Baru</h1>
        <p class="text-sm text-slate-500">Daftarkan akun kasir atau admin baru untuk mengakses sistem.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Nama Lengkap Petugas <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="contoh: budi@laundry.test"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
            </div>

            <!-- Phone -->
            <div>
                <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Nomor WhatsApp / HP
                </label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Peran / Hak Akses (Role) <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer hover:bg-slate-50 transition border-slate-200">
                        <input type="radio" name="role" value="kasir" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('role', 'kasir') === 'kasir' ? 'checked' : '' }}>
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">Kasir / Petugas</span>
                            <span class="block text-xs text-slate-500">Bisa input order cucian, ubah status cucian, dan proses pembayaran kasir.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer hover:bg-slate-50 transition border-slate-200">
                        <input type="radio" name="role" value="admin" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('role') === 'admin' ? 'checked' : '' }}>
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">Admin / Owner</span>
                            <span class="block text-xs text-slate-500">Akses penuh: kelola tarif layanan, kelola akun petugas, dan hapus data.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Passwords -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required minlength="6" placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi"
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    Simpan Petugas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
