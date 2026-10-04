@extends('layouts.app')
@section('title', 'Servicios')
@section('content')
<h2>Servicios</h2>
<div class="card">
    <form method="post" action="{{ route('admin.services.store') }}" class="grid">
        @csrf
        <input name="name" placeholder="Nombre" required>
        <input name="duration_minutes" type="number" placeholder="Minutos" required>
        <input name="price" type="number" step="0.01" placeholder="Precio €" required>
        <button class="btn">Añadir</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Nombre</th><th>Duración</th><th>Precio</th><th></th></tr>
        @foreach($services as $s)
            <tr>
                <td>{{ $s->name }}</td><td>{{ $s->duration_minutes }} min</td><td>{{ number_format($s->price, 2, ',', '.') }} €</td>
                <td><form method="post" action="{{ route('admin.services.destroy', $s) }}">@csrf @method('DELETE')<button class="btn sec">Borrar</button></form></td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
