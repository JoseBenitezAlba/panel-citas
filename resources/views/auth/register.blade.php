@extends('layouts.app')
@section('title', 'Registro')
@section('content')
<div class="card" style="max-width:420px;margin:auto">
    <h2>Crear cuenta</h2>
    <form method="post" action="{{ url('/registro') }}">
        @csrf
        <label>Nombre <input name="name" value="{{ old('name') }}" required></label>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <label>Repite la contraseña <input type="password" name="password_confirmation" required></label>
        <button class="btn">Registrarme</button>
    </form>
</div>
@endsection
