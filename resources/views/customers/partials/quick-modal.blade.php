@php
    $targetSelectId = $targetSelectId ?? 'customer_id';
    $redirectUrl = $redirectUrl ?? null;
@endphp

<!-- Quick Add Customer Modal -->
<div id="customerModal" onclick="if(event.target === this) closeCustomerModal()" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900 leading-tight">Tambah Pelanggan Baru</h3>
                    <p class="text-xs text-slate-500">Pendaftaran cepat data pelanggan laundry.</p>
                </div>
            </div>
            <button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition text-sm font-bold" title="Tutup (Esc)">✕</button>
        </div>

        <div id="modalAlert" class="hidden p-3 rounded-xl text-xs font-medium"></div>

        <form id="quickCustomerForm" onsubmit="event.preventDefault(); submitQuickCustomer();" class="space-y-3.5">
            <div>
                <label for="modal_nama" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Nama Pelanggan <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="modal_nama" required placeholder="Contoh: Rina Anggraini" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label for="modal_no_hp" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    No. HP / WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="modal_no_hp" required placeholder="Contoh: 081298765432" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label for="modal_alamat" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Alamat (Opsional)
                </label>
                <textarea id="modal_alamat" rows="2" placeholder="Contoh: Jl. Kenanga No. 45" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition"></textarea>
            </div>

            <div>
                <label for="modal_catatan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan Khusus (Opsional)
                </label>
                <input type="text" id="modal_catatan" placeholder="Contoh: Pelanggan prioritas / alergi deterjen" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCustomerModal()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitModal" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs">
                    <span>Simpan & Pilih</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const QUICK_CUST_CONFIG = {
        targetSelectId: @json($targetSelectId),
        redirectUrl: @json($redirectUrl),
        quickStoreUrl: '{{ route("customers.quick-store") }}',
        csrfToken: '{{ csrf_token() }}'
    };

    function openCustomerModal() {
        const modal = document.getElementById('customerModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            const input = document.getElementById('modal_nama');
            if (input) input.focus();
        }, 50);
    }

    function closeCustomerModal() {
        const modal = document.getElementById('customerModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const form = document.getElementById('quickCustomerForm');
        if (form) form.reset();
        const alertBox = document.getElementById('modalAlert');
        if (alertBox) {
            alertBox.classList.add('hidden');
            alertBox.innerHTML = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('customerModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeCustomerModal();
            }
        }
    });

    async function submitQuickCustomer() {
        const namaInput = document.getElementById('modal_nama');
        const noHpInput = document.getElementById('modal_no_hp');
        const alamatInput = document.getElementById('modal_alamat');
        const catatanInput = document.getElementById('modal_catatan');
        const alertBox = document.getElementById('modalAlert');
        const btn = document.getElementById('btnSubmitModal');

        const nama = namaInput ? namaInput.value.trim() : '';
        const no_hp = noHpInput ? noHpInput.value.trim() : '';
        const alamat = alamatInput ? alamatInput.value.trim() : '';
        const catatan = catatanInput ? catatanInput.value.trim() : '';

        if (!nama || !no_hp) {
            alertBox.innerHTML = 'Nama pelanggan dan Nomor HP / WhatsApp wajib diisi!';
            alertBox.className = 'p-3 rounded-xl text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
            alertBox.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Menyimpan...</span>
        `;

        try {
            const res = await fetch(QUICK_CUST_CONFIG.quickStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': QUICK_CUST_CONFIG.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nama, no_hp, alamat, catatan })
            });

            const data = await res.json();

            if (res.ok && data.success && data.customer) {
                // If a select element target is specified, append and select
                if (QUICK_CUST_CONFIG.targetSelectId) {
                    const select = document.getElementById(QUICK_CUST_CONFIG.targetSelectId);
                    if (select) {
                        const opt = document.createElement('option');
                        opt.value = data.customer.id;
                        opt.textContent = `${data.customer.nama} (${data.customer.kode_pelanggan} - ${data.customer.no_hp})`;
                        select.appendChild(opt);
                        select.value = String(data.customer.id);
                        opt.selected = true;
                        select.dispatchEvent(new Event('change'));
                    }
                }

                closeCustomerModal();

                // If redirect configured, redirect
                if (QUICK_CUST_CONFIG.redirectUrl) {
                    const targetUrl = QUICK_CUST_CONFIG.redirectUrl.replace(':id', data.customer.id);
                    window.location.href = targetUrl;
                    return;
                }

                showQuickCustomerToast(`Pelanggan "${data.customer.nama}" (${data.customer.kode_pelanggan}) berhasil ditambahkan dan dipilih!`);
            } else {
                let errorMsg = data.message || 'Gagal menyimpan data pelanggan.';
                if (data.errors) {
                    const list = Object.values(data.errors).flat();
                    if (list.length > 0) {
                        errorMsg = list.join('<br>');
                    }
                }
                alertBox.innerHTML = errorMsg;
                alertBox.className = 'p-3 rounded-xl text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            alertBox.innerHTML = 'Terjadi kesalahan sistem saat menghubungi server. Silakan coba lagi.';
            alertBox.className = 'p-3 rounded-xl text-xs font-medium bg-rose-50 border border-rose-200 text-rose-800';
            alertBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    function showQuickCustomerToast(message) {
        let toast = document.getElementById('quickCustomerToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'quickCustomerToast';
            toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 bg-emerald-600 text-white text-xs font-semibold rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-none';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `
            <svg class="w-4 h-4 text-emerald-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>${message}</span>
        `;
        toast.classList.remove('translate-y-2', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
        }, 3500);
    }
</script>
@endpush
