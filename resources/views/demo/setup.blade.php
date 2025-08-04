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

        {{-- STEP 3 --}}
        <div id="step3" class="hidden space-y-6 w-full max-w-md text-center">
            <h2 class="text-xl font-semibold">Пошук пристрою у мережі…</h2>
            <div id="scan-status" class="text-accent">Зачекайте, йде автоматичне підключення…</div>
            <div class="flex justify-center items-center mt-6">
                <svg class="animate-spin h-12 w-12 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>
<script>
axios.defaults.withCredentials = true;

document.addEventListener('DOMContentLoaded', () => {
    const btnScan   = document.getElementById('btn-scan');
    const btnSend   = document.getElementById('btn-send');
    const networks  = document.getElementById('networks');
    const passInput = document.getElementById('wifi-pass');
    const step1     = document.getElementById('step1');
    const step2     = document.getElementById('step2');
    const step3     = document.getElementById('step3');
    const shellyIp  = '192.168.33.1';
    let   chosenSsid = null;

    // Сканування мереж
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

    // Вибір SSID
    networks.addEventListener('click', e => {
        const btn = e.target.closest('[data-ssid]');
        if (!btn) return;

        [...networks.children].forEach(b => b.classList.remove('ring-2', 'ring-accent'));
        btn.classList.add('ring-2', 'ring-accent');
        chosenSsid = btn.dataset.ssid;

        btnSend.classList.remove('hidden');
    });

    // Надсилання конфігурації
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
            
            // Ховаємо step2, показуємо step3
            step2.classList.add('hidden');
            step3.classList.remove('hidden');
            autoDiscoverDevice(); // Запускаємо автоматичний пошук пристрою
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

    // Сканування підмережі для знаходження Shelly
    function autoDiscoverDevice() {
        const scanStatus = document.getElementById('scan-status');
        axios.post('/device/auto-discover')
            .then(resp => {
                const { found, ip, mac } = resp.data;
                if (found) {
                    scanStatus.innerHTML = `<span class="text-green-500">Знайдено пристрій!</span><br>IP: <b>${ip}</b><br>MAC: <b>${mac}</b>`;
                    setTimeout(() => location.href = '/demo', 3000);
                } else {
                    scanStatus.innerHTML = `<span class="text-red-500">Пристрій не знайдено. Перевірте, чи він у мережі!</span>`;
                }
            })
            .catch(e => {
                scanStatus.textContent = 'Сталася помилка під час сканування.';
            });
    }
});
</script>
@endsection
