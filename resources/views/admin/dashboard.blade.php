@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h2>Dashboard</h2>
<div class="grid">
    <div class="card"><div>Citas totales</div><div class="kpi">{{ $total }}</div></div>
    <div class="card"><div>Ingresos confirmados</div><div class="kpi">{{ number_format($revenue, 2, ',', '.') }} €</div></div>
</div>
<div class="grid">
    <div class="card"><h3>Citas por estado</h3><canvas id="estado"></canvas></div>
    <div class="card"><h3>Citas por servicio</h3><canvas id="servicio"></canvas></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    new Chart(document.getElementById('estado'), {
        type: 'doughnut',
        data: { labels: @json($byStatus->keys()), datasets: [{ data: @json($byStatus->values()) }] }
    });
    new Chart(document.getElementById('servicio'), {
        type: 'bar',
        data: { labels: @json($byService->keys()), datasets: [{ label: 'Citas', data: @json($byService->values()) }] },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endsection
