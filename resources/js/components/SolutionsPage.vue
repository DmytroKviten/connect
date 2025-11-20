<template>
  <div class="max-w-6xl mx-auto px-4 md:px-6 py-12 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
      <div>
        <p class="text-cyan-300 text-xs font-semibold uppercase tracking-[0.2em] mb-2">Use cases</p>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Готові рішення на базі Smart Power</h1>
        <p class="text-slate-300 max-w-2xl">
          Ми даємо набір сценаріїв поза коробкою: від віддалених перезапусків до балансування навантаження та push-конфігурацій для партій пристроїв.
        </p>
      </div>
      <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 text-emerald-100 px-4 py-3 text-sm font-semibold">
        API готовий: /api/ingest, /api/devices, /api/monitoring
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">01</p>
        <h3 class="text-lg font-semibold text-white mb-2">Моніторинг критичних ліній</h3>
        <p class="text-sm text-slate-300">Тримайте параметри в межах: алерти при піках, історія за 30 днів, zoomable графіки.</p>
      </div>
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">02</p>
        <h3 class="text-lg font-semibold text-white mb-2">Віддалені перезапуски</h3>
        <p class="text-sm text-slate-300">Команди reboot/factory reset через API або UI. Плануйте з кроном чи ручним тригером.</p>
      </div>
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">03</p>
        <h3 class="text-lg font-semibold text-white mb-2">Партійне налаштування</h3>
        <p class="text-sm text-slate-300">Push Wi‑Fi конфіги на десятки пристроїв, стежимо за статусом і повторними спробами.</p>
      </div>
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">04</p>
        <h3 class="text-lg font-semibold text-white mb-2">Аналітика споживання</h3>
        <p class="text-sm text-slate-300">Агрегації по користувачу/девайсу, середні й пікові значення, швидкий експорт.</p>
      </div>
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">05</p>
        <h3 class="text-lg font-semibold text-white mb-2">Демо-каталог</h3>
        <p class="text-sm text-slate-300">Готові демо-пристрої, щоб показати продукт без інтеграції заліза.</p>
      </div>
      <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 shadow-sm">
        <p class="text-xs text-slate-400 mb-1">06</p>
        <h3 class="text-lg font-semibold text-white mb-2">Безпека на периметрі</h3>
        <p class="text-sm text-slate-300">Rate-limit на ingest, контроль токенів, розмежування ролей через Sanctum.</p>
      </div>
    </div>
  </div>
</template>
