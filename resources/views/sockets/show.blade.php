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

    {{-- Кнопки керування --}}
    <div x-data="controls()" x-init="init()" class="flex flex-wrap gap-4 mb-10 justify-center">

        <button @click="toggle()"
                x-text="state === true ? 'Вимкнути розетку' : (state === false ? 'Увімкнути розетку' : 'Завантаження…')"
                class="px-6 py-2 rounded bg-accentDark hover:bg-accent font-semibold text-white shadow"
                :disabled="loading">
        </button>

        <button @click="reboot()"
                class="px-6 py-2 rounded bg-blue-800 hover:bg-blue-600 font-semibold text-white shadow"
                :disabled="loading">
            Перезапустити пристрій
        </button>

        <button @click="factoryReset()"
                class="px-6 py-2 rounded bg-red-700 hover:bg-red-500 font-semibold text-white shadow"
                :disabled="loading">
            Скинути до заводських
        </button>

        <button @click="getInfo()"
                class="px-6 py-2 rounded bg-green-700 hover:bg-green-500 font-semibold text-white shadow"
                :disabled="loading">
            Отримати інформацію
        </button>
    </div>

    {{-- Відповіді від API --}}
    <div x-show="message"
         x-text="message"
         class="text-center text-sm text-accent mb-8"></div>

    {{-- Графік останніх 20 вимірів потужності --}}
    <canvas id="chart" height="120"></canvas>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
let chart = null;

// --- AJAX-графік ---
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chart');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Потужність, Вт',
                data: [],
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { x: { display: true } }
        }
    });
    updateChart();
    setInterval(updateChart, 10000);
});

async function updateChart() {
    try {
        const res = await axios.get('/device/{{ $device->id }}/chart-data');
        chart.data.labels = res.data.labels;
        chart.data.datasets[0].data = res.data.power;
        chart.update();
    } catch (e) {
        console.warn('Chart update error:', e);
    }
}

// --- LIVE блок ---
function live() {
    return {
        power: '—',
        voltage: '—',
        energy: '—',
        start() {
            this.fetchNow();
            setInterval(() => this.fetchNow(), 10000);
        },
        async fetchNow() {
            try {
                const r = await axios.get('/device/{{ $device->id }}/live');
                if (r.data) {
                    this.power = r.data.power ?? '—';
                    this.voltage = r.data.voltage ?? '—';
                    this.energy = r.data.energy ?? '—';
                }
            } catch (e) { console.error(e); }
        }
    };
}

// --- Контролі керування ---
function controls() {
    return {
        state: null,
        loading: false,
        message: '',
        init() { this.getState(); },

        async getState() {
            this.loading = true;
            try {
                const res = await axios.get('/device/{{ $device->id }}/live');
                this.state = res.data.state;
            } catch {
                this.state = null;
            }
            this.loading = false;
        },

        async toggle() {
            this.loading = true;
            this.message = '';
            try {
                const url = '/device/{{ $device->id }}/switch';
                const res = await axios.post(url, { on: !this.state });
                this.state = !this.state;
                this.message = 'Стан пристрою змінено!';
            } catch (e) {
                this.message = 'Помилка: ' + (e.response?.data?.error || 'невідомо');
            }
            this.loading = false;
        },

        async reboot() {
            if (!confirm('Перезапустити пристрій?')) return;
            this.loading = true;
            this.message = '';
            try {
                await axios.post('/device/{{ $device->id }}/reboot');
                this.message = 'Пристрій перезапущено!';
            } catch (e) {
                this.message = 'Помилка перезапуску';
            }
            this.loading = false;
        },

        async factoryReset() {
            if (!confirm('Скинути пристрій до заводських?')) return;
            this.loading = true;
            this.message = '';
            try {
                await axios.post('/device/{{ $device->id }}/factory-reset');
                this.message = 'Скидання виконано!';
            } catch (e) {
                this.message = 'Помилка скидання';
            }
            this.loading = false;
        },

        async getInfo() {
            this.loading = true;
            this.message = '';
            try {
                const res = await axios.get('/device/{{ $device->id }}/info');
                this.message = 'Інфо: ' + JSON.stringify(res.data);
            } catch (e) {
                this.message = 'Не вдалося отримати інфо';
            }
            this.loading = false;
        }
    }
}
</script>
@endsection

