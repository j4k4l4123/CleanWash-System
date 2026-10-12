<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $order = null;
        $search = trim($request->input('q', ''));

        if ($search) {
            $order = Order::with(['customer', 'service', 'statusLogs', 'payment'])
                ->where('kode_order', $search)
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('no_hp', $search);
                })
                ->latest('tgl_masuk')
                ->first();
        }

        return view('tracking.index', compact('order', 'search'));
    }

    public function show(string $kode_order)
    {
        $order = Order::with(['customer', 'service', 'statusLogs', 'payment'])
            ->where('kode_order', $kode_order)
            ->firstOrFail();

        return view('tracking.show', compact('order'));
    }
}
