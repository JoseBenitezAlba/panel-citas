<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel de citas')</title>
    <style>
        :root { --c:#2563eb; --bg:#f3f4f6; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: var(--bg); margin: 0; color: #111827; }
        nav { background: #111827; padding: .8rem 1.5rem; display: flex; gap: 1rem; align-items: center; }
        nav a, nav button { color: #e5e7eb; text-decoration: none; background: none; border: 0; font: inherit; cursor: pointer; }
        nav .sp { flex: 1; }
        main { max-width: 900px; margin: 1.5rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.2rem; margin-bottom: 1rem; box-shadow: 0 1px 3px #0001; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .5rem; border-bottom: 1px solid #e5e7eb; }
        input, select, textarea { width: 100%; padding: .5rem; margin: .25rem 0 .8rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
        .btn { background: var(--c); color: #fff; border: 0; padding: .5rem 1rem; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font: inherit; }
        .btn.sec { background: #6b7280; }
        .ok { background: #dcfce7; padding: .6rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .err { background: #fee2e2; padding: .6rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .kpi { font-size: 2rem; font-weight: 700; }
        .badge { padding: .1rem .5rem; border-radius: 99px; font-size: .8rem; background: #e5e7eb; }
    </style>
</head>
<body>
<nav>
    <strong style="color:#fff">Panel de citas</strong>
    @auth
        <a href="{{ route('appointments.index') }}">Mis citas</a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.appointments') }}">Todas las citas</a>
            <a href="{{ route('admin.services') }}">Servicios</a>
        @endif
        <span class="sp"></span>
        <span style="color:#9ca3af">{{ auth()->user()->name }}</span>
        <form method="post" action="{{ route('logout') }}">@csrf <button>Salir</button></form>
    @else
        <span class="sp"></span>
        <a href="{{ route('login') }}">Entrar</a>
        <a href="{{ route('register') }}">Registrarse</a>
    @endauth
</nav>
<main>
    @if(session('status')) <div class="ok">{{ session('status') }}</div> @endif
    @if($errors->any()) <div class="err">@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div> @endif
    @yield('content')
</main>
</body>
</html>
