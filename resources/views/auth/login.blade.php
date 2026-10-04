@extends('layouts.app')
@section('title', 'Entrar')
@section('content')
<div class="card" style="max-width:420px;margin:auto">
    <h2>Entrar</h2>
    <form method="post" action="{{ url('/login') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <button class="btn">Entrar</button>
    </form>
</div>
@endsection
