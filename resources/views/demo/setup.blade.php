{{-- resources/views/demo/setup.blade.php --}}
@extends('layouts.base')

@section('content')
    @include('layouts.header')

    <div class="min-h-screen flex flex-col items-center justify-center bg-base text-white p-6">

        {{-- STEP 1 --}}
        <div id="step1" class="space-y-6 w-full max-w-md">
            <h1 class="text-2xl font-bold text-center">Підключення розетки</h1>
            <p>1. Під’єднайтесь до Wi-Fi <code>shellyplug-xxxx</code>.</p>

            <button id="btn-scan"
                    type="button"
                    class="w-full py-2 rounded bg-accentDark hover:bg-accent font-semibold">
                Я підключився
            </button>
        </div>

        {{-- STEP 2 --}}
        <div id="step2" class="hidden space-y-6 w-full max-w-md">
            <h2 class="text-xl font-semibold text-center">Виберіть Wi-Fi</h2>

            <div id="networks" class="space-y-2"></div>

            <input id="wifi-pass"
                   type="password"
                   placeholder="Пароль до Wi-Fi"
                   class="w-full px-3 py-2 rounded text-black">

            <button id="btn-send"
                    type="button"
                    class="w-full py-2 rounded bg-accentDark hover:bg-accent font-semibold hidden">
                Під’єднати
            </button>
        </div>

    </div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>
<script>
/* один раз – щоб XHR надсилали cookie */
axios.defaults.withCredentials = true;

document.addEventListener('DOMContentLoaded', () => {

    /* елементи */
    const btnScan   = document.getElementById('btn-scan');
    const btnSend   = document.getElementById('btn-send');
    const networks  = document.getElementById('networks');
    const passInput = document.getElementById('wifi-pass');
    const step1     = document.getElementById('step1');
    const step2     = document.getElementById('step2');
    const shellyIp  = '192.168.33.1';
    let   chosenSsid = null;

    /* скан мереж */
    btnScan.addEventListener('click', async () => {
        btnScan.disabled = true;
        btnScan.textContent = 'Сканую…';

        step1.classList.add('hidden');
        step2.classList.remove('hidden');

        try {
            const { data } = await axios.post('/device/scan', { ip: shellyIp });

            if (!data.length) {
                networks.textContent = 'SSID не знайдено';
                return;
            }
            networks.innerHTML = data.map(ssid => `
                <button type="button"
                        class="w-full py-2 rounded border border-accent hover:bg-accent/20"
                        data-ssid="${ssid}">${ssid}</button>`).join('');
        } catch (e) {
            console.error('Помилка сканування:', e);
            networks.textContent = 'Shelly не відповідає';
        }
    });

    /* вибір SSID */
    networks.addEventListener('click', e => {
        const btn = e.target.closest('[data-ssid]');
        if (!btn) return;

        [...networks.children].forEach(b => b.classList.remove('ring-2', 'ring-accent'));
        btn.classList.add('ring-2', 'ring-accent');
        chosenSsid = btn.dataset.ssid;

        btnSend.classList.remove('hidden');
    });

    /* надсилання конфігурації */
    btnSend.addEventListener('click', async () => {
        if (!chosenSsid) return alert('Виберіть мережу');

        btnSend.disabled = true;
        btnSend.textContent = 'Надсилаю…';

        try {
            await axios.post('/device/config', {
                ip:   shellyIp,
                ssid: chosenSsid,
                password: passInput.value.trim()
            });

            btnSend.textContent = 'Готово! Перепідключіть Wi-Fi';
            setTimeout(() => location.href = '/demo', 3000);

        } catch (e) {
            if (e.response?.status === 401) {
                alert('Сесія закінчилась – увійдіть знову');
                location.href = '/login';
                return;
            }
            console.error('Помилка підключення:', e);
            alert('Помилка: ' + (e.response?.data?.message || 'невідомо'));
            btnSend.textContent = 'Помилка';
            btnSend.disabled = false;
        }
    });
});
</script>
@endsection
