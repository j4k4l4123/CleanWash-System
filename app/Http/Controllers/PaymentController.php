<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.customer', 'order.service']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pembayaran', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('kode_order', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($cq) use ($search) {
                                $cq->where('nama', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($metode = $request->input('metode_pembayaran')) {
            $query->where('metode_pembayaran', $metode);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('tgl_bayar', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('tgl_bayar', '<=', $dateTo);
        }

        $payments = $query->latest('tgl_bayar')->paginate(10)->withQueryString();

        $revenueStats = Cache::remember('payments_revenue_stats', 60, function () {
            $raw = Payment::where('status', 'lunas')
                ->selectRaw("
                    COALESCE(SUM(jumlah_bayar), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN metode_pembayaran = 'tunai' THEN jumlah_bayar ELSE 0 END), 0) as cash_revenue,
                    COALESCE(SUM(CASE WHEN metode_pembayaran = 'qris' THEN jumlah_bayar ELSE 0 END), 0) as qris_revenue,
                    COALESCE(SUM(CASE WHEN metode_pembayaran = 'transfer' THEN jumlah_bayar ELSE 0 END), 0) as transfer_revenue
                ")->first();

            return [
                'total_revenue' => (float) ($raw->total_revenue ?? 0),
                'cash_revenue' => (float) ($raw->cash_revenue ?? 0),
                'qris_revenue' => (float) ($raw->qris_revenue ?? 0),
                'transfer_revenue' => (float) ($raw->transfer_revenue ?? 0),
            ];
        });

        $totalRevenue = (float) ($revenueStats['total_revenue'] ?? 0);
        $cashRevenue = (float) ($revenueStats['cash_revenue'] ?? 0);
        $qrisRevenue = (float) ($revenueStats['qris_revenue'] ?? 0);
        $transferRevenue = (float) ($revenueStats['transfer_revenue'] ?? 0);

        return view('payments.index', compact(
            'payments',
            'totalRevenue',
            'cashRevenue',
            'qrisRevenue',
            'transferRevenue'
        ));
    }

    public function store(Request $request, Order $order)
    {
        if ($order->status_pembayaran === 'lunas') {
            return back()->with('error', "Order {$order->kode_order} sudah berstatus lunas!");
        }

        $validated = $request->validate([
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'uang_diterima' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $totalHarga = $order->total_harga;
        $uangDiterima = $validated['uang_diterima'] ? (float) $validated['uang_diterima'] : $totalHarga;

        if ($validated['metode_pembayaran'] === 'tunai' && $uangDiterima < $totalHarga) {
            return back()->with('error', 'Uang yang diterima kurang dari total tagihan!');
        }

        $kembalian = max(0, $uangDiterima - $totalHarga);
        $dateCode = Carbon::now()->format('Ymd');
        $todayStart = Carbon::today()->startOfDay();
        $paymentCount = Payment::where('tgl_bayar', '>=', $todayStart)->count();
        $kodePembayaran = 'INV-'.$dateCode.'-'.str_pad($paymentCount + 1, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $order->update([
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => $validated['metode_pembayaran'],
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'kode_pembayaran' => $kodePembayaran,
                'jumlah_bayar' => $totalHarga,
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => 'lunas',
                'tgl_bayar' => Carbon::now(),
                'catatan' => $validated['catatan'] ?? 'Pelunasan tagihan laundry',
            ]);

            DB::commit();
            $this->clearPaymentCache();

            return redirect()->route('orders.show', $order)
                ->with('success', "Pembayaran untuk {$order->kode_order} berhasil diproses (No Invoice: {$payment->kode_pembayaran})!");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['order.customer', 'order.service']);

        return view('payments.receipt', compact('payment'));
    }

    protected function clearPaymentCache(): void
    {
        Cache::forget('dashboard_stats');
        Cache::forget('payments_revenue_stats');
    }
}
