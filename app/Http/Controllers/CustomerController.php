<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders');

        if ($search = $request->input('search')) {
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

        $latestCust = Customer::latest('id')->first();
        $nextId = $latestCust ? ($latestCust->id + 1) : 1;
        $validated['kode_pelanggan'] = 'CUST-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Pelanggan {$customer->nama} ({$customer->kode_pelanggan}) berhasil ditambahkan!");
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders.service', 'orders.payment']);
        $totalOrders = $customer->orders->count();
        $totalSpending = $customer->orders->where('status_pembayaran', 'lunas')->sum('total_harga');
        $activeOrders = $customer->orders->whereNotIn('status_cucian', ['selesai', 'dibatalkan'])->count();

        return view('customers.show', compact('customer', 'totalOrders', 'totalSpending', 'activeOrders'));
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

        return redirect()->route('customers.show', $customer)
            ->with('success', "Data pelanggan {$customer->nama} berhasil diperbarui!");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->nama;
        $customer->delete();

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

        $latestCust = Customer::latest('id')->first();
        $nextId = $latestCust ? ($latestCust->id + 1) : 1;
        $validated['kode_pelanggan'] = 'CUST-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => 'Pelanggan baru berhasil ditambahkan!',
        ]);
    }
}
