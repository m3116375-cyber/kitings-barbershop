<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function index()
    {
        return view('admin.services.index', ['services' => Service::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.services.form', ['service' => new Service(['is_active' => true, 'duration_minutes' => 30])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        // Layanan yang pernah dipesan tidak dihapus agar riwayat & laporan tetap utuh.
        if (BookingService::where('service_id', $service->id)->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('success', 'Layanan pernah dipesan, jadi hanya dinonaktifkan (riwayat tetap aman).');
        }

        $service->delete();

        return back()->with('success', 'Layanan dihapus.');
    }
}
