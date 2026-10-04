<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $byStatus = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $byService = Appointment::join('services', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', '!=', 'cancelled')
            ->select('services.name', DB::raw('count(*) as total'))
            ->groupBy('services.name')->orderByDesc('total')->pluck('total', 'name');

        $revenue = Appointment::join('services', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'confirmed')->sum('services.price');

        return view('admin.dashboard', [
            'byStatus' => $byStatus,
            'byService' => $byService,
            'revenue' => $revenue,
            'total' => $byStatus->sum(),
        ]);
    }
}
