@extends('layouts.app')
@section('title', 'Mis citas')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Mis citas</h2>
    <a class="btn" href="{{ route('appointments.create') }}">Nueva cita</a>
</div>
<div class="card">
    <table>
        <tr><th>Servicio</th><th>Fecha</th><th>Estado</th><th></th></tr>
        @forelse($appointments as $a)
            <tr>
                <td>{{ $a->service->name }}</td>
                <td>{{ $a->starts_at->format('d/m/Y H:i') }}</td>
                <td><span class="badge">{{ $a->status }}</span></td>
                <td>
                    @if($a->status !== 'cancelled')
                        <form method="post" action="{{ route('appointments.cancel', $a) }}">@csrf @method('PATCH')<button class="btn sec">Cancelar</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Todavía no tienes citas.</td></tr>
        @endforelse
    </table>
    {{ $appointments->links() }}
</div>
@endsection
