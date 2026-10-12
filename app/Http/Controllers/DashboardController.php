<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = Cache::remember('dashboard_stats', 30, function () use ($today, $startOfMonth) {
            $totalCustomers = Customer::count();

            $todayStart = $today->copy()->startOfDay();
            $orderStats = Order::selectRaw("
                COUNT(CASE WHEN tgl_masuk >= ? THEN 1 END) as orders_today,
                COUNT(CASE WHEN tgl_masuk >= ? THEN 1 END) as orders_month,
                COUNT(CASE WHEN status_cucian NOT IN ('selesai', 'dibatalkan') THEN 1 END) as active_orders,
                COUNT(CASE WHEN status_cucian = 'siap_diambil' THEN 1 END) as ready_for_pickup,
                COUNT(CASE WHEN status_cucian = 'diterima' THEN 1 END) as diterima,
                COUNT(CASE WHEN status_cucian = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN status_pembayaran = 'belum_lunas' THEN 1 END) as unpaid
            ", [$todayStart, $startOfMonth])->first();

            $paymentStats = Payment::where('status', 'lunas')
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN tgl_bayar >= ? THEN jumlah_bayar ELSE 0 END), 0) as revenue_today,
                    COALESCE(SUM(CASE WHEN tgl_bayar >= ? THEN jumlah_bayar ELSE 0 END), 0) as revenue_month
                ', [$todayStart, $startOfMonth])->first();

            return [
                'totalCustomers' => (int) $totalCustomers,
                'ordersTodayCount' => (int) ($orderStats->orders_today ?? 0),
                'ordersThisMonthCount' => (int) ($orderStats->orders_month ?? 0),
                'activeOrdersCount' => (int) ($orderStats->active_orders ?? 0),
                'readyForPickupCount' => (int) ($orderStats->ready_for_pickup ?? 0),
                'unpaidCount' => (int) ($orderStats->unpaid ?? 0),
                'revenueToday' => (float) ($paymentStats->revenue_today ?? 0),
                'revenueThisMonth' => (float) ($paymentStats->revenue_month ?? 0),
                'statusCounts' => [
                    'diterima' => (int) ($orderStats->diterima ?? 0),
                    'siap_diambil' => (int) ($orderStats->ready_for_pickup ?? 0),
                    'selesai' => (int) ($orderStats->selesai ?? 0),
                ],
            ];
        });

        // Recent orders
        $recentOrders = Order::with(['customer', 'service'])
            ->latest('tgl_masuk')
            ->take(8)
            ->get();

        return view('dashboard.index', [
            'totalCustomers' => $stats['totalCustomers'],
            'ordersTodayCount' => $stats['ordersTodayCount'],
            'ordersThisMonthCount' => $stats['ordersThisMonthCount'],
            'revenueToday' => $stats['revenueToday'],
            'revenueThisMonth' => $stats['revenueThisMonth'],
            'activeOrdersCount' => $stats['activeOrdersCount'],
            'readyForPickupCount' => $stats['readyForPickupCount'],
            'unpaidCount' => $stats['unpaidCount'],
            'recentOrders' => $recentOrders,
            'statusCounts' => $stats['statusCounts'],
        ]);
    }
}
