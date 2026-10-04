<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = $request->user()->appointments()
            ->with('service')->orderBy('starts_at')->paginate(10);

        return view('appointments.index', compact('appointments'));
    }

    public function create(): View
    {
        return view('appointments.create', ['services' => Service::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $taken = Appointment::where('service_id', $data['service_id'])
            ->where('starts_at', $data['starts_at'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($taken) {
            return back()->withErrors(['starts_at' => 'Esa hora ya está ocupada para este servicio.'])->withInput();
        }

        $request->user()->appointments()->create($data);

        return redirect()->route('appointments.index')->with('status', 'Cita solicitada.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->user_id === $request->user()->id, 403);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('status', 'Cita cancelada.');
    }
}
