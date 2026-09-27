@extends('layouts.app')

@section('title', 'Penerimaan Order Cucian Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Terima Cucian Baru</h1>
            <p class="text-sm text-slate-500">Input transaksi laundry, penimbangan berat, dan pembayaran kasir.</p>
        </div>
        <a href="{{ route('orders.index') }}" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Main Order Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('orders.store') }}" method="POST" id="orderForm" class="space-y-6">
            @csrf

            <!-- Section 1: Customer Selection -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="customer_id" class="text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Pilih Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" onclick="openCustomerModal()" class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>+ Tambah Pelanggan Baru</span>
                    </button>
                </div>
                <select id="customer_id" name="customer_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">-- Pilih Nama Pelanggan --</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}" {{ (old('customer_id', request('customer_id')) == $cust->id) ? 'selected' : '' }}>
                            {{ $cust->nama }} ({{ $cust->kode_pelanggan }} - {{ $cust->no_hp }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Section 2: Service & Weight -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="service_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Layanan / Paket Laundry <span class="text-rose-500">*</span>
                    </label>
                    <select id="service_id" name="service_id" required onchange="calculateTotal()" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach ($services as $svc)
                            <option value="{{ $svc->id }}" 
                                    data-harga="{{ $svc->harga }}" 
                                    data-jenis="{{ $svc->jenis }}"
                                    data-durasi="{{ $svc->durasi_jam }}"
                                    {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                                {{ $svc->nama_layanan }} - Rp {{ number_format($svc->harga, 0, ',', '.') }}/{{ $svc->jenis === 'kiloan' ? 'kg' : 'pcs' }} ({{ $svc->durasi_jam }} jam)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="berat_atau_jumlah" id="qtyLabel" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Berat (Kg) / Jumlah (Pcs) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.1" min="0.1" id="berat_atau_jumlah" name="berat_atau_jumlah" value="{{ old('berat_atau_jumlah', '1') }}" required oninput="calculateTotal()" placeholder="Contoh: 3.5" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Live Calculation Summary Card -->
            <div class="p-4 rounded-xl bg-brand-50/60 border border-brand-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-medium text-brand-800">Perkiraan Biaya & Estimasi Selesai</span>
                    <div class="text-xs text-slate-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="estimasiInfo">Pilih layanan untuk melihat estimasi selesai</span>
                    </div>
                </div>
                <div class="text-right sm:text-right">
                    <div class="text-xs text-slate-500">Total Biaya Cucian</div>
                    <div class="text-2xl font-bold text-brand-900" id="totalHargaDisplay">Rp 0</div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="catatan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Cucian / Instruksi Khusus
                </label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Contoh: Pakaian putih dipisah, jangan gunakan pewangi berlebih, noda kopi di lengan baju." class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('catatan') }}</textarea>
            </div>

            <!-- Section 3: Payment Section -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="bayar_sekarang" name="bayar_sekarang" value="1" {{ old('bayar_sekarang') ? 'checked' : '' }} onchange="togglePaymentBox()" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                    <label for="bayar_sekarang" class="text-sm font-semibold text-slate-800 cursor-pointer">
                        Bayar Sekarang di Kasir (Status: Lunas)
                    </label>
                </div>

                <div id="paymentFields" class="{{ old('bayar_sekarang') ? '' : 'hidden' }} space-y-4 pt-3 border-t border-slate-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="metode_pembayaran" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Metode Pembayaran
                            </label>
                            <select id="metode_pembayaran" name="metode_pembayaran" onchange="toggleKembalian()" class="w-full px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition">
                                <option value="tunai" {{ old('metode_pembayaran') == 'tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                                <option value="qris" {{ old('metode_pembayaran') == 'qris' ? 'selected' : '' }}>QRIS (ShopeePay/GoPay/OVO/BCA)</option>
                                <option value="transfer" {{ old('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                            </select>
                        </div>

                        <div id="uangDiterimaWrapper">
                            <label for="uang_diterima" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Uang Diterima (Rp)
                            </label>
                            <input type="number" id="uang_diterima" name="uang_diterima" value="{{ old('uang_diterima') }}" oninput="calculateKembalian()" placeholder="Jumlah uang cash" class="w-full px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 transition">
                        </div>
                    </div>

                    <div id="kembalianAlert" class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
                        <span>Kembalian Kasir:</span>
                        <span id="kembalianDisplay" class="text-sm font-bold font-mono">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    Simpan & Proses Order
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Quick Add Customer Modal -->
<div id="customerModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 class="text-base font-semibold text-slate-900">Tambah Pelanggan Baru</h3>
            <button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
        </div>

        <div id="modalAlert" class="hidden p-3 rounded-lg text-xs font-medium"></div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Pelanggan *</label>
                <input type="text" id="modal_nama" placeholder="Contoh: Rina Anggraini" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">No. HP / WhatsApp *</label>
                <input type="text" id="modal_no_hp" placeholder="Contoh: 081298765432" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Alamat (Opsional)</label>
                <textarea id="modal_alamat" rows="2" placeholder="Contoh: Jl. Kenanga No. 45" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition"></textarea>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="closeCustomerModal()" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                Batal
            </button>
            <button type="button" onclick="submitQuickCustomer()" id="btnSubmitModal" class="px-4 py-1.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                Simpan & Pilih
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentTotal = 0;

    function calculateTotal() {
        const serviceSelect = document.getElementById('service_id');
        const qtyInput = document.getElementById('berat_atau_jumlah');
        const qtyLabel = document.getElementById('qtyLabel');
        const estimasiInfo = document.getElementById('estimasiInfo');
        const totalDisplay = document.getElementById('totalHargaDisplay');

        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            totalDisplay.innerText = 'Rp 0';
            estimasiInfo.innerText = 'Pilih layanan untuk melihat estimasi selesai';
            currentTotal = 0;
            return;
        }

        const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
        const jenis = selectedOption.getAttribute('data-jenis');
        const durasi = parseInt(selectedOption.getAttribute('data-durasi')) || 24;
        const qty = parseFloat(qtyInput.value) || 0;

        qtyLabel.innerText = jenis === 'kiloan' ? 'Berat Cucian (Kg) *' : 'Jumlah Pakaian (Pcs) *';

        currentTotal = Math.round(harga * qty);
        totalDisplay.innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');

        // Estimate completion
        const date = new Date();
        date.setHours(date.getHours() + durasi);
        const options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
        estimasiInfo.innerText = 'Selesai dalam ±' + durasi + ' jam (Estimasi: ' + date.toLocaleDateString('id-ID', options) + ')';

        calculateKembalian();
    }

    function togglePaymentBox() {
        const check = document.getElementById('bayar_sekarang');
        const box = document.getElementById('paymentFields');
        if (check.checked) {
            box.classList.remove('hidden');
            const uangInput = document.getElementById('uang_diterima');
            if (!uangInput.value && currentTotal > 0) {
                uangInput.value = currentTotal;
            }
            calculateKembalian();
        } else {
            box.classList.add('hidden');
        }
    }

    function toggleKembalian() {
        const method = document.getElementById('metode_pembayaran').value;
        const wrapper = document.getElementById('uangDiterimaWrapper');
        const kembalianAlert = document.getElementById('kembalianAlert');
        if (method !== 'tunai') {
            wrapper.classList.add('hidden');
            kembalianAlert.classList.add('hidden');
        } else {
            wrapper.classList.remove('hidden');
            kembalianAlert.classList.remove('hidden');
            calculateKembalian();
        }
    }

    function calculateKembalian() {
        const method = document.getElementById('metode_pembayaran').value;
        if (method !== 'tunai') return;

        const uangInput = document.getElementById('uang_diterima');
        const received = parseFloat(uangInput.value) || 0;
        const kembalian = Math.max(0, received - currentTotal);
        document.getElementById('kembalianDisplay').innerText = 'Rp ' + kembalian.toLocaleString('id-ID');
    }

    // Modal Quick Customer
    function openCustomerModal() {
        document.getElementById('customerModal').classList.remove('hidden');
        document.getElementById('modal_nama').focus();
    }

    function closeCustomerModal() {
        document.getElementById('customerModal').classList.add('hidden');
        document.getElementById('modal_nama').value = '';
        document.getElementById('modal_no_hp').value = '';
        document.getElementById('modal_alamat').value = '';
        document.getElementById('modalAlert').classList.add('hidden');
    }

    async function submitQuickCustomer() {
        const nama = document.getElementById('modal_nama').value.trim();
        const no_hp = document.getElementById('modal_no_hp').value.trim();
        const alamat = document.getElementById('modal_alamat').value.trim();
        const alertBox = document.getElementById('modalAlert');
        const btn = document.getElementById('btnSubmitModal');

        if (!nama || !no_hp) {
            alertBox.innerText = 'Nama dan No. HP wajib diisi!';
            alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
            alertBox.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        try {
            const res = await fetch('{{ route("customers.quick-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nama, no_hp, alamat })
            });

            const data = await res.json();

            if (data.success && data.customer) {
                const select = document.getElementById('customer_id');
                const opt = document.createElement('option');
                opt.value = data.customer.id;
                opt.innerText = `${data.customer.nama} (${data.customer.kode_pelanggan} - ${data.customer.no_hp})`;
                opt.selected = true;
                select.appendChild(opt);

                closeCustomerModal();
            } else {
                alertBox.innerText = data.message || 'Gagal menyimpan data';
                alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            alertBox.innerText = 'Terjadi kesalahan sistem.';
            alertBox.className = 'p-3 rounded-lg text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
            alertBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan & Pilih';
        }
    }

    // Run on load
    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endpush
