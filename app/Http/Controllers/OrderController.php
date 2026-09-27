<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'service', 'payment']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_order', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusCucian = $request->input('status_cucian')) {
            $query->where('status_cucian', $statusCucian);
        }

        if ($statusPembayaran = $request->input('status_pembayaran')) {
            $query->where('status_pembayaran', $statusPembayaran);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('tgl_masuk', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('tgl_masuk', '<=', $dateTo);
        }

        $orders = $query->latest('tgl_masuk')->paginate(10)->withQueryString();
        $statuses = Order::listStatuses();

        return view('orders.index', compact('orders', 'statuses'));
    }

    public function create()
    {
        $customers = Customer::orderBy('nama')->get();
        $services = Service::where('is_active', true)->orderBy('nama_layanan')->get();

        return view('orders.create', compact('customers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'berat_atau_jumlah' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string',
            'bayar_sekarang' => 'nullable|boolean',
            'metode_pembayaran' => 'required_if:bayar_sekarang,1|nullable|in:tunai,transfer,qris',
            'uang_diterima' => 'nullable|numeric|min:0',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $hargaPerSatuan = $service->harga;
        $totalHarga = round($validated['berat_atau_jumlah'] * $hargaPerSatuan, 2);

        // Generate Order Code ORD-YYYYMMDD-XXX
        $dateCode = Carbon::now()->format('Ymd');
        $todayOrdersCount = Order::whereDate('tgl_masuk', Carbon::today())->count();
        $kodeOrder = 'ORD-'.$dateCode.'-'.str_pad($todayOrdersCount + 1, 3, '0', STR_PAD_LEFT);

        $now = Carbon::now();
        $estimasiSelesai = $now->copy()->addHours($service->durasi_jam ?? 24);

        $bayarSekarang = $request->boolean('bayar_sekarang');
        $statusPembayaran = $bayarSekarang ? 'lunas' : 'belum_lunas';
        $metodePembayaran = $bayarSekarang ? $validated['metode_pembayaran'] : null;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'kode_order' => $kodeOrder,
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_id'],
                'berat_atau_jumlah' => $validated['berat_atau_jumlah'],
                'harga_per_satuan' => $hargaPerSatuan,
                'total_harga' => $totalHarga,
                'tgl_masuk' => $now,
                'estimasi_selesai' => $estimasiSelesai,
                'status_cucian' => 'diterima',
                'status_pembayaran' => $statusPembayaran,
                'metode_pembayaran' => $metodePembayaran,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Create initial status log
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'diterima',
                'keterangan' => 'Order baru diterima di kasir ('.$service->nama_layanan.')',
                'diupdate_oleh' => auth()->user()->name ?? 'Kasir / Admin',
            ]);

            // If paid now, record payment
            if ($bayarSekarang) {
                $uangDiterima = $validated['uang_diterima'] ? (float) $validated['uang_diterima'] : $totalHarga;
                $kembalian = max(0, $uangDiterima - $totalHarga);

                $paymentCount = Payment::whereDate('tgl_bayar', Carbon::today())->count();
                $kodePembayaran = 'INV-'.$dateCode.'-'.str_pad($paymentCount + 1, 3, '0', STR_PAD_LEFT);

                Payment::create([
                    'order_id' => $order->id,
                    'kode_pembayaran' => $kodePembayaran,
                    'jumlah_bayar' => $totalHarga,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'metode_pembayaran' => $metodePembayaran,
                    'status' => 'lunas',
                    'tgl_bayar' => $now,
                    'catatan' => 'Pembayaran saat order masuk',
                ]);
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', "Order {$order->kode_order} berhasil dibuat!");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal membuat order: '.$e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'service', 'statusLogs', 'payments']);
        $statuses = Order::listStatuses();

        return view('orders.show', compact('order', 'statuses'));
    }

    public function edit(Order $order)
    {
        $customers = Customer::orderBy('nama')->get();
        $services = Service::where('is_active', true)->orderBy('nama_layanan')->get();

        return view('orders.edit', compact('order', 'customers', 'services'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'berat_atau_jumlah' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $hargaPerSatuan = $service->harga;
        $totalHarga = round($validated['berat_atau_jumlah'] * $hargaPerSatuan, 2);

        $order->update([
            'customer_id' => $validated['customer_id'],
            'service_id' => $validated['service_id'],
            'berat_atau_jumlah' => $validated['berat_atau_jumlah'],
            'harga_per_satuan' => $hargaPerSatuan,
            'total_harga' => $totalHarga,
            'catatan' => $validated['catatan'],
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', "Data order {$order->kode_order} berhasil diperbarui!");
    }

    public function destroy(Order $order)
    {
        $code = $order->kode_order;
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', "Order {$code} berhasil dihapus.");
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status_cucian' => 'required|in:diterima,siap_diambil,selesai',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $newStatus = $validated['status_cucian'];
        $now = Carbon::now();

        $updateData = ['status_cucian' => $newStatus];

        if ($newStatus === 'siap_diambil' && ! $order->tgl_selesai) {
            $updateData['tgl_selesai'] = $now;
        }

        if ($newStatus === 'selesai') {
            if (! $order->tgl_selesai) {
                $updateData['tgl_selesai'] = $now;
            }
            $updateData['tgl_diambil'] = $now;
        }

        $order->update($updateData);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'keterangan' => $validated['keterangan'] ?? 'Status diubah ke '.$order->status_label,
            'diupdate_oleh' => auth()->user()->name ?? 'Kasir / Admin',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', "Status cucian berhasil diperbarui menjadi {$order->status_label}!");
    }

    public function print(Order $order)
    {
        $order->load(['customer', 'service', 'payment']);

        return view('orders.print', compact('order'));
    }
}
