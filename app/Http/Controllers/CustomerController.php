<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Customer::withCount('orders');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $maxId = Customer::max('id') ?? 0;
        $validated['kode_pelanggan'] = 'CUST-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create($validated);
        $this->clearCustomerCache();

        return redirect()->route('customers.show', $customer)
            ->with('success', "Pelanggan {$customer->nama} ({$customer->kode_pelanggan}) berhasil ditambahkan!");
    }

    public function show(Customer $customer)
    {
        $stats = $customer->orders()
            ->selectRaw("
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN status_pembayaran = 'lunas' THEN total_harga ELSE 0 END), 0) as total_spending,
                COUNT(CASE WHEN status_cucian NOT IN ('selesai', 'dibatalkan') THEN 1 END) as active_orders
            ")
            ->first();

        $totalOrders = (int) ($stats->total_orders ?? 0);
        $totalSpending = (float) ($stats->total_spending ?? 0);
        $activeOrders = (int) ($stats->active_orders ?? 0);

        $orders = $customer->orders()
            ->with(['service', 'payment'])
            ->latest('tgl_masuk')
            ->paginate(10)
            ->withQueryString();

        return view('customers.show', compact('customer', 'totalOrders', 'totalSpending', 'activeOrders', 'orders'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $customer->update($validated);
        $this->clearCustomerCache();

        return redirect()->route('customers.show', $customer)
            ->with('success', "Data pelanggan {$customer->nama} berhasil diperbarui!");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->nama;
        $customer->delete();
        $this->clearCustomerCache();

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$name} berhasil dihapus.");
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $maxId = Customer::max('id') ?? 0;
        $validated['kode_pelanggan'] = 'CUST-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create($validated);
        $this->clearCustomerCache();

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => 'Pelanggan baru berhasil ditambahkan!',
        ]);
    }

    protected function clearCustomerCache(): void
    {
        Cache::forget('active_customers_list');
        Cache::forget('dashboard_stats');
    }
}
