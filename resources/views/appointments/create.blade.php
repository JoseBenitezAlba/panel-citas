@extends('layouts.app')
@section('title', 'Nueva cita')
@section('content')
<div class="card">
    <h2>Nueva cita</h2>
    <form method="post" action="{{ route('appointments.store') }}">
        @csrf
        <label>Servicio
            <select name="service_id" required>
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->duration_minutes }} min, {{ number_format($s->price, 2, ',', '.') }} €)</option>
                @endforeach
            </select>
        </label>
        <label>Fecha y hora <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required></label>
        <label>Notas <textarea name="notes">{{ old('notes') }}</textarea></label>
        <button class="btn">Solicitar</button>
    </form>
</div>
@endsection
