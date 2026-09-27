<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalCustomers = Customer::count();
        $ordersTodayCount = Order::whereDate('tgl_masuk', $today)->count();
        $ordersThisMonthCount = Order::where('tgl_masuk', '>=', $startOfMonth)->count();

        $revenueToday = Payment::whereDate('tgl_bayar', $today)->where('status', 'lunas')->sum('jumlah_bayar');
        $revenueThisMonth = Payment::where('tgl_bayar', '>=', $startOfMonth)->where('status', 'lunas')->sum('jumlah_bayar');

        $activeOrdersCount = Order::whereNotIn('status_cucian', ['selesai', 'dibatalkan'])->count();
        $readyForPickupCount = Order::where('status_cucian', 'siap_diambil')->count();
        $unpaidCount = Order::where('status_pembayaran', 'belum_lunas')->count();

        // Recent orders
        $recentOrders = Order::with(['customer', 'service'])
            ->latest('tgl_masuk')
            ->take(8)
            ->get();

        // Status counters
        $statusCounts = [
            'diterima' => Order::where('status_cucian', 'diterima')->count(),
            'siap_diambil' => $readyForPickupCount,
            'selesai' => Order::where('status_cucian', 'selesai')->count(),
        ];

        return view('dashboard.index', compact(
            'totalCustomers',
            'ordersTodayCount',
            'ordersThisMonthCount',
            'revenueToday',
            'revenueThisMonth',
            'activeOrdersCount',
            'readyForPickupCount',
            'unpaidCount',
            'recentOrders',
            'statusCounts'
        ));
    }
}
