@extends('layouts.app')
@section('title', 'Todas las citas')
@section('content')
<h2>Todas las citas</h2>
<div class="card">
    <table>
        <tr><th>Cliente</th><th>Servicio</th><th>Fecha</th><th>Estado</th></tr>
        @foreach($appointments as $a)
            <tr>
                <td>{{ $a->user->name }}</td>
                <td>{{ $a->service->name }}</td>
                <td>{{ $a->starts_at->format('d/m/Y H:i') }}</td>
                <td>
                    <form method="post" action="{{ route('admin.appointments.status', $a) }}" style="display:flex;gap:.5rem">
                        @csrf @method('PATCH')
                        <select name="status" style="margin:0">
                            @foreach(\App\Models\Appointment::STATUSES as $st)
                                <option value="{{ $st }}" @selected($a->status === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                        <button class="btn">Guardar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
    {{ $appointments->links() }}
</div>
@endsection
