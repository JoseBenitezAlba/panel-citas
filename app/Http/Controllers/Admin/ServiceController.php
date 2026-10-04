<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services', ['services' => Service::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]));

        return back()->with('status', 'Servicio creado.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->appointments()->exists()) {
            return back()->withErrors(['service' => 'No se puede borrar un servicio con citas.']);
        }

        $service->delete();

        return back()->with('status', 'Servicio borrado.');
    }

    public function appointments(): View
    {
        return view('admin.appointments', [
            'appointments' => Appointment::with(['user', 'service'])->orderByDesc('starts_at')->paginate(15),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', Appointment::STATUSES)]]);
        $appointment->update($data);

        return back()->with('status', 'Estado actualizado.');
    }
}
