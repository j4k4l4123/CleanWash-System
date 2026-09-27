<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportData($request);

        return view('reports.index', $data);
    }

    public function print(Request $request)
    {
        $data = $this->getReportData($request);

        return view('reports.print', $data);
    }

    private function getReportData(Request $request): array
    {
        $filter = $request->input('period', 'this_month');
        $startDate = null;
        $endDate = null;

        switch ($filter) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $startDate = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : Carbon::now()->startOfMonth();
                $endDate = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : Carbon::now()->endOfDay();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $filter = 'this_month';
        }

        // Query orders in range
        $ordersQuery = Order::with(['customer', 'service', 'payment'])
            ->whereBetween('tgl_masuk', [$startDate, $endDate]);

        if ($serviceId = $request->input('service_id')) {
            $ordersQuery->where('service_id', $serviceId);
        }

        if ($paymentStatus = $request->input('status_pembayaran')) {
            $ordersQuery->where('status_pembayaran', $paymentStatus);
        }

        $orders = $ordersQuery->latest('tgl_masuk')->get();

        // Metrics
        $totalOrders = $orders->count();
        $totalOmzet = $orders->where('status_pembayaran', 'lunas')->sum('total_harga');
        $totalPiutang = $orders->where('status_pembayaran', 'belum_lunas')->sum('total_harga');
        $totalSelesai = $orders->where('status_cucian', 'selesai')->count();
        $totalProses = $orders->whereNotIn('status_cucian', ['selesai', 'dibatalkan'])->count();
        $totalKiloan = $orders->where('service.jenis', 'kiloan')->sum('berat_atau_jumlah');

        // Revenue by payment method in period
        $payments = Payment::whereBetween('tgl_bayar', [$startDate, $endDate])->get();
        $methodBreakdown = [
            'tunai' => $payments->where('metode_pembayaran', 'tunai')->sum('jumlah_bayar'),
            'qris' => $payments->where('metode_pembayaran', 'qris')->sum('jumlah_bayar'),
            'transfer' => $payments->where('metode_pembayaran', 'transfer')->sum('jumlah_bayar'),
        ];

        // Service popularity breakdown
        $serviceBreakdown = Service::withCount(['orders' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tgl_masuk', [$startDate, $endDate]);
        }])->get();

        $services = Service::where('is_active', true)->get();

        return compact(
            'filter',
            'startDate',
            'endDate',
            'orders',
            'totalOrders',
            'totalOmzet',
            'totalPiutang',
            'totalSelesai',
            'totalProses',
            'totalKiloan',
            'methodBreakdown',
            'serviceBreakdown',
            'services'
        );
    }
}
