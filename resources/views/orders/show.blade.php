@extends('layouts.app')

@section('title', 'Detail Order ' . $order->kode_order)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="font-mono text-sm font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-200">
                    {{ $order->kode_order }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                    {{ $order->status_label }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $order->payment_badge_class }}">
                    {{ $order->status_pembayaran === 'lunas' ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1.5">
                Order masuk pada {{ $order->tgl_masuk->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @php
                $rawPhone = preg_replace('/[^0-9]/', '', $order->customer->no_hp ?? '');
                $cleanPhone = str_starts_with($rawPhone, '0') ? '62' . substr($rawPhone, 1) : $rawPhone;
                $waText = "Halo Kak {$order->customer->nama},\n\nUpdate pesanan cucian Anda di CleanWash Laundry:\nNo. Order: {$order->kode_order}\nStatus: {$order->status_label}\nTotal Tagihan: Rp " . number_format($order->total_harga, 0, ',', '.') . " (" . ($order->status_pembayaran === 'lunas' ? 'LUNAS' : 'BELUM LUNAS') . ")\n\nCek progres cucian online di:\n" . url('/tracking/' . $order->kode_order) . "\n\nTerima kasih!";
            @endphp
            @if($cleanPhone)
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ rawurlencode($waText) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-xl hover:bg-emerald-100 transition shadow-xs" title="Kirim Notifikasi Status via WhatsApp">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.84 2.79.84h.001c3.181 0 5.768-2.586 5.768-5.766 0-3.18-2.587-5.766-5.768-5.766zm9.969 5.828c0 5.519-4.481 10-10 10-1.748 0-3.385-.45-4.817-1.238l-7.183 1.88 1.914-6.994c-.878-1.492-1.384-3.232-1.384-5.088 0-5.519 4.481-10 10-10s10 4.481 10 10z"/>
                    </svg>
                    <span>Kirim WA</span>
                </a>
            @endif
            <a href="{{ route('orders.print', $order) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Nota</span>
            </a>
            <button type="button" onclick="openStatusModal()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Update Status Cucian</span>
            </button>
            <a href="{{ route('orders.index') }}" class="px-3 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Visual Status Flow Progress -->
    @php
        $flowSteps = [
            'diterima' => '1. Diterima',
            'siap_diambil' => '2. Siap Diambil',
            'selesai' => '3. Selesai',
        ];
        $stepKeys = array_keys($flowSteps);
        $currentIndex = array_search($order->status_cucian, $stepKeys);
        if ($currentIndex === false && $order->status_cucian === 'dibatalkan') {
            $currentIndex = -1;
        }
    @endphp

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Progres Pengerjaan Laundry</h2>

        @if($order->status_cucian === 'dibatalkan')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium">
                Pesanan cucian ini telah dibatalkan.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach ($flowSteps as $stepKey => $stepTitle)
                    @php
                        $stepIndex = array_search($stepKey, $stepKeys);
                        $isPassed = $currentIndex !== false && $stepIndex <= $currentIndex;
                        $isCurrent = $stepKey === $order->status_cucian;
                    @endphp
                    <div class="p-3.5 rounded-xl border text-center transition {{ $isCurrent ? 'border-brand-500 bg-brand-50/80 shadow-xs font-semibold text-brand-900' : ($isPassed ? 'border-emerald-200 bg-emerald-50/40 text-emerald-800' : 'border-slate-100 bg-slate-50 text-slate-400') }}">
                        <div class="flex items-center justify-center mb-1.5">
                            @if($isPassed)
                                <svg class="w-5 h-5 {{ $isCurrent ? 'text-brand-600' : 'text-emerald-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <div class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                            @endif
                        </div>
                        <div class="text-xs sm:text-sm font-medium">{{ $stepTitle }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Order Details & Payment Settlement Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Order Information & Laundry Items -->
        <div class="md:col-span-2 space-y-6">

            <!-- Detail Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-sm font-semibold text-slate-900 pb-2 border-b border-slate-100">
                    Rincian Order & Layanan
                </h2>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Layanan Terpilih</span>
                        <div class="font-semibold text-slate-800 mt-0.5">{{ $order->service->nama_layanan }}</div>
                        <div class="text-xs text-slate-500">{{ $order->service->deskripsi }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Jenis & Durasi</span>
                        <div class="font-semibold text-slate-800 mt-0.5 uppercase">{{ $order->service->jenis }}</div>
                        <div class="text-xs text-slate-500">Estimasi durasi: {{ $order->service->durasi_jam }} jam</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Berat / Jumlah</span>
                        <div class="text-base font-bold text-slate-900 mt-0.5">
                            {{ $order->berat_atau_jumlah }} {{ $order->service->jenis === 'kiloan' ? 'Kilogram (Kg)' : 'Pcs' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Tarif per Satuan</span>
                        <div class="text-base font-semibold text-slate-800 mt-0.5">
                            Rp {{ number_format($order->harga_per_satuan, 0, ',', '.') }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Estimasi Selesai</span>
                        <div class="text-sm font-medium text-slate-800 mt-0.5">
                            {{ $order->estimasi_selesai ? $order->estimasi_selesai->isoFormat('D MMM Y, HH:mm') : '-' }} WIB
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Waktu Selesai / Diambil</span>
                        <div class="text-sm font-medium text-slate-800 mt-0.5">
                            {{ $order->tgl_diambil ? $order->tgl_diambil->isoFormat('D MMM Y, HH:mm') : ($order->tgl_selesai ? $order->tgl_selesai->isoFormat('D MMM Y, HH:mm') . ' (Siap Ambil)' : 'Belum Selesai') }}
                        </div>
                    </div>
                </div>

                @if($order->catatan)
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                        <span class="font-bold">Catatan Kasir / Permintaan Khusus:</span>
                        <p class="mt-0.5">{{ $order->catatan }}</p>
                    </div>
                @endif

                <!-- Total Row -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-500">Total Tagihan</span>
                    <span class="text-2xl font-bold text-slate-900">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Status History Timeline -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-sm font-semibold text-slate-900 pb-2 border-b border-slate-100">
                    Riwayat Perubahan Status (Status Log)
                </h2>

                <div class="space-y-3">
                    @forelse ($order->statusLogs as $log)
                        <div class="flex items-start gap-3 text-xs">
                            <div class="w-2 h-2 rounded-full bg-brand-600 mt-1.5 flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold uppercase tracking-wider text-slate-800">{{ str_replace('_', ' ', $log->status) }}</span>
                                    <span class="text-slate-400 font-mono">{{ $log->created_at->format('d/m/Y H:i') }} WIB</span>
                                </div>
                                <div class="text-slate-600 mt-0.5">{{ $log->keterangan }}</div>
                                <div class="text-[11px] text-slate-400 italic">Oleh: {{ $log->diupdate_oleh }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada riwayat perubahan status.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Column 3: Customer Card & Payment Card -->
        <div class="space-y-6">

            <!-- Customer Summary Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Data Pelanggan</h3>
                    <a href="{{ route('customers.show', $order->customer) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                        Lihat Profil
                    </a>
                </div>

                <div class="space-y-1.5 text-sm">
                    <div class="font-bold text-slate-900">{{ $order->customer->nama }}</div>
                    <div class="font-mono text-xs text-brand-700 font-medium">{{ $order->customer->kode_pelanggan }}</div>
                    <div class="text-xs text-slate-600 flex items-center justify-between gap-1">
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ rawurlencode($waText) }}" target="_blank" class="flex items-center gap-1 text-slate-700 hover:text-emerald-700 font-medium hover:underline" title="Chat WhatsApp Pelanggan">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.84 2.79.84h.001c3.181 0 5.768-2.586 5.768-5.766 0-3.18-2.587-5.766-5.768-5.766zm9.969 5.828c0 5.519-4.481 10-10 10-1.748 0-3.385-.45-4.817-1.238l-7.183 1.88 1.914-6.994c-.878-1.492-1.384-3.232-1.384-5.088 0-5.519 4.481-10 10-10s10 4.481 10 10z"/>
                            </svg>
                            <span>{{ $order->customer->no_hp }}</span>
                        </a>
                        <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">WA Aktif</span>
                    </div>
                    @if($order->customer->alamat)
                        <div class="text-xs text-slate-500 pt-1 border-t border-slate-100">{{ $order->customer->alamat }}</div>
                    @endif
                </div>
            </div>

            <!-- Payment Status & Settlement Box -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Pembayaran</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $order->payment_badge_class }}">
                        {{ $order->status_pembayaran === 'lunas' ? 'LUNAS' : 'BELUM LUNAS' }}
                    </span>
                </div>

                @if($order->status_pembayaran === 'lunas')
                    @php $pay = $order->payment; @endphp
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 space-y-2 text-xs text-emerald-900">
                        <div class="flex items-center justify-between font-mono">
                            <span>No Invoice:</span>
                            <span class="font-bold">{{ $pay ? $pay->kode_pembayaran : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Metode:</span>
                            <span class="font-semibold uppercase">{{ $order->metode_pembayaran ?? ($pay ? $pay->metode_pembayaran : '-') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Jumlah Bayar:</span>
                            <span class="font-bold font-mono">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>
                        @if($pay && $pay->uang_diterima && $pay->metode_pembayaran === 'tunai')
                            <div class="flex items-center justify-between text-slate-600 border-t border-emerald-200 pt-1">
                                <span>Uang Diterima:</span>
                                <span>Rp {{ number_format($pay->uang_diterima, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Kembalian:</span>
                                <span>Rp {{ number_format($pay->kembalian, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-2 text-slate-500 text-[11px]">
                            Dibayar pada: {{ $pay ? $pay->tgl_bayar->isoFormat('D MMM Y, HH:mm') : $order->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB
                        </div>
                    </div>
                @else
                    <!-- Settle Payment Form -->
                    <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200 space-y-3">
                        <div class="text-xs text-rose-800 font-medium">
                            Tagihan sebesar <span class="font-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span> belum dibayar.
                        </div>

                        <form action="{{ route('payments.store', $order) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Metode Bayar</label>
                                <select name="metode_pembayaran" id="payMethodSelect" onchange="togglePayInputs()" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                                    <option value="tunai">Tunai (Cash)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer Bank</option>
                                </select>
                            </div>

                            <div id="uangDiterimaRow">
                                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Uang Diterima (Rp)</label>
                                <input type="number" name="uang_diterima" id="settleUangDiterima" value="{{ $order->total_harga }}" min="{{ $order->total_harga }}" step="500" oninput="calcSettleKembalian()" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                                <div id="settleKembalianText" class="text-[11px] text-emerald-700 font-semibold mt-1">
                                    Kembalian: Rp 0
                                </div>
                            </div>

                            <button type="submit" class="w-full py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs">
                                Konfirmasi Pembayaran Lunas
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Danger Zone: Delete Order -->
            <div class="pt-2 flex items-center justify-between text-xs text-slate-400">
                <a href="{{ route('orders.edit', $order) }}" class="text-slate-600 hover:text-slate-900 underline">
                    Edit Data Order
                </a>
                @if(Auth::user()->isAdmin())
                    <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Hapus pesanan ini? Seluruh riwayat dan status akan dihapus permanen.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-500 hover:text-rose-700 hover:underline">
                            Hapus Order
                        </button>
                    </form>
                @endif
            </div>

        </div>

    </div>

</div>

<!-- Modal Update Status Cucian -->
<div id="statusModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 class="text-base font-semibold text-slate-900">Perbarui Status Cucian</h3>
            <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
        </div>

        <form action="{{ route('orders.update-status', $order) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Pilih Status Baru <span class="text-rose-500">*</span>
                </label>
                <select name="status_cucian" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" {{ $order->status_cucian === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan / Keterangan (Opsional)
                </label>
                <input type="text" name="keterangan" placeholder="Contoh: Pakaian sudah bersih, rapi, dan siap diambil" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const totalHargaOrder = {{ $order->total_harga }};

    function openStatusModal() {
        document.getElementById('statusModal').classList.remove('hidden');
    }
    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    function togglePayInputs() {
        const method = document.getElementById('payMethodSelect').value;
        const row = document.getElementById('uangDiterimaRow');
        if (method !== 'tunai') {
            row.classList.add('hidden');
        } else {
            row.classList.remove('hidden');
        }
    }

    function calcSettleKembalian() {
        const val = parseFloat(document.getElementById('settleUangDiterima').value) || 0;
        const change = Math.max(0, val - totalHargaOrder);
        document.getElementById('settleKembalianText').innerText = 'Kembalian: Rp ' + change.toLocaleString('id-ID');
    }
</script>
@endpush
