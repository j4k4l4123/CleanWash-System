<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('orders')->orderBy('jenis')->orderBy('harga')->get();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'jenis' => 'required|in:kiloan,satuan',
            'harga' => 'required|numeric|min:0',
            'durasi_jam' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service = Service::create($validated);
        $this->clearServiceCache();

        return redirect()->route('services.index')
            ->with('success', "Layanan {$service->nama_layanan} berhasil ditambahkan!");
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'jenis' => 'required|in:kiloan,satuan',
            'harga' => 'required|numeric|min:0',
            'durasi_jam' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);
        $this->clearServiceCache();

        return redirect()->route('services.index')
            ->with('success', "Layanan {$service->nama_layanan} berhasil diperbarui!");
    }

    public function destroy(Service $service)
    {
        if ($service->orders()->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'Layanan tidak dapat dihapus karena sudah memiliki riwayat order. Anda dapat menonaktifkannya.');
        }

        $service->delete();
        $this->clearServiceCache();

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    public function toggle(Service $service)
    {
        $service->update(['is_active' => ! $service->is_active]);
        $this->clearServiceCache();
        $statusText = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('services.index')
            ->with('success', "Layanan {$service->nama_layanan} berhasil {$statusText}.");
    }

    protected function clearServiceCache(): void
    {
        Cache::forget('active_services');
    }
}
