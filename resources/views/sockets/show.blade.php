@extends('layouts.base')

@section('content')
<div class="max-w-2xl mx-auto mt-10">

    <h1 class="text-2xl font-bold mb-6">
        {{ $device->name }} ({{ $device->model }})
    </h1>

    {{-- Лайв‑показники --}}
    <div x-data="live()" x-init="start()"
         class="grid grid-cols-3 gap-4 mb-8 text-center">
        <div class="p-4 bg-neutral-800 rounded">
            <p class="text-sm text-neutral-400">Потужність</p>
            <p class="text-xl font-semibold" x-text="power + ' W'">—</p>
        </div>
        <div class="p-4 bg-neutral-800 rounded">
            <p class="text-sm text-neutral-400">Напруга</p>
            <p class="text-xl font-semibold" x-text="voltage + ' V'">—</p>
        </div>
        <div class="p-4 bg-neutral-800 rounded">
            <p class="text-sm text-neutral-400">Енергія</p>
            <p class="text-xl font-semibold" x-text="energy + ' Wh'">—</p>
        </div>
    </div>

    {{-- Графік останніх 20 вимірів потужності --}}
    <canvas id="chart" height="120"></canvas>
</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@php
    $labels = $readings->pluck('taken_at')
                       ->map(fn ($d) => $d->format('H:i:s'));
    $powers = $readings->pluck('power');
@endphp

<script>
/* ---------- Chart ---------- */
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Вт',
                data: @json($powers),
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { x: { display:false } }
        }
    });
});

/* ---------- live‑block ---------- */
function live() {
    return {
        power: '—',
        voltage: '—',
        energy: '—',
        start() {
            this.fetchNow();
            setInterval(() => this.fetchNow(), 10_000);
        },
        async fetchNow() {
            try {
                const r = await axios.get('/device/{{ $device->id }}/latest');
                Object.assign(this, r.data);
            } catch (e) { console.error(e); }
        }
    };
}
</script>
@endsection
