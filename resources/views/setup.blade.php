@extends('layouts.app')
@section('title','Майстер підключення')

{{-- --- лише для цієї сторінки: manifest та Tailwind --}}
@section('head')
  <link rel="manifest" href="/manifest.webmanifest">
  <script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center
            bg-black text-white p-6">

  {{-- STEP 1 --}}
  <div id="step1" class="space-y-6 w-full max-w-md">
    <h1 class="text-2xl font-bold text-center">Підключення розетки</h1>
    <p>
      1. Під’єднайтесь до Wi-Fi мережі <code>shellyplug-xxxx</code>
      (тут сама розетка роздає доступ).
    </p>
    <button id="btn-scan" class="btn-primary w-full">Я підключився</button>
  </div>

  {{-- STEP 2 --}}
  <div id="step2" class="hidden space-y-6 w-full max-w-md">
    <h2 class="text-xl font-semibold text-center">Доступні мережі Wi-Fi</h2>

    <div id="networks" class="space-y-2"></div>

    <input id="wifi-pass" type="password"
           placeholder="Пароль до Wi-Fi"
           class="w-full px-3 py-2 bg-surface rounded text-black focus:outline-none">

    <button id="btn-send" class="btn-primary w-full hidden">Під’єднати</button>
  </div>

</div>
@endsection

{{----- локальний JS + Service-Worker реєстрація тільки тут --}}
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
/* ===== Service-Worker лише на /setup ============================ */
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js').catch(console.error);
}

/* ======= DOM елементи ========================================== */
const btnScan   = document.getElementById('btn-scan');
const btnSend   = document.getElementById('btn-send');
const networks  = document.getElementById('networks');
const passInput = document.getElementById('wifi-pass');
const shellyIp  = '192.168.33.1';    // IP AP-режиму за замовчанням
let   chosenSsid = null;

/* ---- КРОК 1 → крок 2 + скан через БЕКЕНД ---------------------- */
btnScan.addEventListener('click', async () => {
  btnScan.disabled = true;
  btnScan.textContent = 'Сканую…';

  step1.classList.add('hidden');
  step2.classList.remove('hidden');

  try {
    /* ← ідемо на власний сервер, він уже в тій самій мережі, що й браузер */
    const { data: ssids } = await axios.post('/api/shelly/scan', {
      ip: shellyIp                                // 192.168.33.1
    });

    if (!ssids.length) {
      networks.textContent = 'SSID не знайдено';
      return;
    }

    networks.innerHTML = ssids.map(s =>
      `<button class="btn-outline w-full" data-ssid="${s}">${s}</button>`
    ).join('');
  } catch (e) {
    networks.textContent = 'Shelly не відповідає';
  }
});

/* ---- вибір SSID ------------------------------------------------ */
networks.addEventListener('click', e => {
  if (!e.target.dataset.ssid) return;

  [...networks.children].forEach(b => b.classList.remove('ring-2','ring-accent'));
  e.target.classList.add('ring-2','ring-accent');
  chosenSsid = e.target.dataset.ssid;

  btnSend.classList.remove('hidden');
});

/* ---- надсилання Wi-Fi конфіг ---------------------------------- */
btnSend.addEventListener('click', async () => {
  const pwd = passInput.value.trim();      // може бути порожнім
  if (!chosenSsid) {                      // перевіряємо тільки SSID
    alert('Виберіть SSID'); return;
  }

  btnSend.disabled = true;
  btnSend.textContent = 'Надсилаю…';

  try {
    await axios.post('/api/shelly/config', {
      ip:  shellyIp,
      ssid: chosenSsid,
      password: pwd          // якщо порожній — так і надсилаємо ''
    });

    btnSend.textContent = 'Готово! Перепідключіть Wi-Fi';
  } catch {
    btnSend.textContent = 'Помилка';
  }
});
</script>
@endsection
