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
                                <button type="button"
                                        onclick="openStatusDropdown(event, this, {{ $order->id }}, '{{ $order->kode_order }}', '{{ addslashes($order->customer->nama) }}', '{{ $order->status_cucian }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $order->status_badge_class }} hover:shadow-xs hover:ring-2 hover:ring-slate-300/60 transition cursor-pointer group"
                                        title="Klik untuk mengubah status cucian">
                                    <span>{{ $order->status_label }}</span>
                                    <svg class="w-3 h-3 opacity-60 group-hover:opacity-100 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
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

<!-- Global Floating Status Dropdown Menu -->
<div id="statusDropdownMenu" class="hidden fixed z-50 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 w-48 text-xs transition-all duration-150">
    <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
        Pilih Status Cucian
    </div>
    <div class="p-1 space-y-0.5">
        <button type="button" 
                data-status="diterima" 
                onclick="handleStatusSelect('diterima', 'Diterima')" 
                class="status-opt w-full text-left px-3 py-2 rounded-xl flex items-center justify-between text-slate-700 hover:bg-amber-50 hover:text-amber-900 transition group cursor-pointer">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-amber-500"></span>
                <span class="font-medium">Diterima</span>
            </span>
            <span class="check-icon text-amber-600 font-bold hidden text-xs">✓</span>
        </button>

        <button type="button" 
                data-status="siap_diambil" 
                onclick="handleStatusSelect('siap_diambil', 'Siap Diambil')" 
                class="status-opt w-full text-left px-3 py-2 rounded-xl flex items-center justify-between text-slate-700 hover:bg-teal-50 hover:text-teal-900 transition group cursor-pointer">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-400 border border-teal-500"></span>
                <span class="font-medium">Siap Diambil</span>
            </span>
            <span class="check-icon text-teal-600 font-bold hidden text-xs">✓</span>
        </button>

        <button type="button" 
                data-status="selesai" 
                onclick="handleStatusSelect('selesai', 'Selesai')" 
                class="status-opt w-full text-left px-3 py-2 rounded-xl flex items-center justify-between text-slate-700 hover:bg-emerald-50 hover:text-emerald-900 transition group cursor-pointer">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 border border-emerald-500"></span>
                <span class="font-medium">Selesai</span>
            </span>
            <span class="check-icon text-emerald-600 font-bold hidden text-xs">✓</span>
        </button>
    </div>
</div>

<!-- Modal Konfirmasi Ubah Status -->
<div id="confirmStatusModal" onclick="if(event.target === this) closeConfirmModal()" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-slate-900">Konfirmasi Ubah Status</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pastikan proses cucian telah sesuai sebelum mengubah status ini.
                </p>
            </div>
            <button type="button" onclick="closeConfirmModal()" class="text-slate-400 hover:text-slate-600 text-sm font-bold p-1">✕</button>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Kode Order:</span>
                <span class="font-mono font-bold text-slate-800" id="modalOrderCode">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Pelanggan:</span>
                <span class="font-medium text-slate-800" id="modalCustomerName">-</span>
            </div>
            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                <span class="text-slate-500">Perubahan:</span>
                <div class="inline-flex items-center gap-1.5 font-semibold">
                    <span id="modalOldStatus" class="px-2 py-0.5 rounded-full text-[11px] border">-</span>
                    <span class="text-slate-400">➔</span>
                    <span id="modalNewStatus" class="px-2 py-0.5 rounded-full text-[11px] border">-</span>
                </div>
            </div>
        </div>

        <p class="text-sm font-medium text-slate-700">
            Apakah Anda yakin untuk mengubah status orderan ini?
        </p>

        <form id="confirmStatusForm" method="POST" action="">
            @csrf
            <input type="hidden" name="status_cucian" id="modalStatusInput">
            <input type="hidden" name="redirect_to" value="index">

            <div class="mb-4">
                <label for="modalKeterangan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan / Keterangan (Opsional)
                </label>
                <input type="text" name="keterangan" id="modalKeterangan" placeholder="Contoh: Pakaian sudah disetrika dan rapi" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </button>
                <button type="submit" id="btnConfirmSubmit" class="px-5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs flex items-center gap-1.5">
                    Ya, Ubah Status
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentOrderData = null;

    function openStatusDropdown(e, btn, orderId, orderCode, customerName, currentStatus) {
        e.stopPropagation();

        currentOrderData = {
            orderId: orderId,
            orderCode: orderCode,
            customerName: customerName,
            currentStatus: currentStatus
        };

        const dropdown = document.getElementById('statusDropdownMenu');

        // Highlight status saat ini
        const statusOptions = dropdown.querySelectorAll('.status-opt');
        statusOptions.forEach(opt => {
            const optStatus = opt.getAttribute('data-status');
            const checkIcon = opt.querySelector('.check-icon');
            if (optStatus === currentStatus) {
                checkIcon.classList.remove('hidden');
                opt.classList.add('bg-slate-100/70', 'font-semibold');
            } else {
                checkIcon.classList.add('hidden');
                opt.classList.remove('bg-slate-100/70', 'font-semibold');
            }
        });

        dropdown.classList.remove('hidden');
        const rect = btn.getBoundingClientRect();

        let top = rect.bottom + 6;
        let left = rect.left;

        if (left + dropdown.offsetWidth > window.innerWidth - 12) {
            left = window.innerWidth - dropdown.offsetWidth - 12;
        }
        if (top + dropdown.offsetHeight > window.innerHeight - 12) {
            top = rect.top - dropdown.offsetHeight - 6;
        }

        dropdown.style.top = `${top}px`;
        dropdown.style.left = `${left}px`;
    }

    function closeStatusDropdown() {
        const dropdown = document.getElementById('statusDropdownMenu');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }

    function handleStatusSelect(newStatus, newStatusLabel) {
        if (!currentOrderData) return;

        const { orderId, orderCode, customerName, currentStatus } = currentOrderData;
        closeStatusDropdown();

        if (newStatus === currentStatus) {
            return;
        }

        const statusMeta = {
            'diterima': { label: 'Diterima', badge: 'bg-amber-100 text-amber-800 border-amber-300' },
            'siap_diambil': { label: 'Siap Diambil', badge: 'bg-teal-100 text-teal-800 border-teal-300' },
            'selesai': { label: 'Selesai', badge: 'bg-emerald-100 text-emerald-800 border-emerald-300' }
        };

        const oldMeta = statusMeta[currentStatus] || { label: currentStatus, badge: 'bg-slate-100 text-slate-800 border-slate-300' };
        const newMeta = statusMeta[newStatus] || { label: newStatusLabel, badge: 'bg-slate-100 text-slate-800 border-slate-300' };

        document.getElementById('modalOrderCode').textContent = orderCode;
        document.getElementById('modalCustomerName').textContent = customerName;

        const oldBadge = document.getElementById('modalOldStatus');
        oldBadge.textContent = oldMeta.label;
        oldBadge.className = `px-2 py-0.5 rounded-full text-[11px] border ${oldMeta.badge}`;

        const newBadge = document.getElementById('modalNewStatus');
        newBadge.textContent = newMeta.label;
        newBadge.className = `px-2 py-0.5 rounded-full text-[11px] border ${newMeta.badge}`;

        document.getElementById('modalStatusInput').value = newStatus;
        document.getElementById('modalKeterangan').value = '';

        const form = document.getElementById('confirmStatusForm');
        form.action = `/orders/${orderId}/update-status`;

        document.getElementById('confirmStatusModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmStatusModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Klik di luar dropdown untuk menutup
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('statusDropdownMenu');
        if (dropdown && !dropdown.contains(e.target)) {
            closeStatusDropdown();
        }
    });

    // Escape untuk menutup modal & dropdown
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStatusDropdown();
            closeConfirmModal();
        }
    });

    // Cegah double submit
    document.getElementById('confirmStatusForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnConfirmSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';
        }
    });
</script>
@endpush
