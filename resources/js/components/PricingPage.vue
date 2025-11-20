<template>
  <div class="max-w-5xl mx-auto px-4 md:px-6 py-12">
    <div class="text-center mb-8">
      <p class="text-amber-300 text-xs font-semibold uppercase tracking-[0.2em] mb-2">Plans</p>
      <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Прозорі тарифи без прихованих платежів</h1>
      <p class="text-slate-300">Оплачуйте лише за кількість активних пристроїв та опції автоматизації.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
      <div class="p-6 rounded-2xl bg-slate-900/70 border border-white/5 shadow">
        <p class="text-slate-400 text-sm mb-1">Starter</p>
        <p class="text-3xl font-bold text-white mb-2">$19<span class="text-sm text-slate-400">/місяць</span></p>
        <ul class="space-y-2 text-sm text-slate-300">
          <li>До 20 пристроїв</li>
          <li>Моніторинг та графіки</li>
          <li>Alerts email</li>
        </ul>
        <RouterLink to="/register" class="mt-4 inline-flex px-4 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400 transition">
          Спробувати
        </RouterLink>
      </div>
      <div class="p-6 rounded-2xl bg-slate-900/80 border border-emerald-400/40 shadow-lg shadow-emerald-500/15">
        <p class="text-emerald-300 text-sm mb-1">Growth</p>
        <p class="text-3xl font-bold text-white mb-2">$59<span class="text-sm text-slate-400">/місяць</span></p>
        <ul class="space-y-2 text-sm text-slate-200">
          <li>До 200 пристроїв</li>
          <li>Автоматизація (reboot/factory reset)</li>
          <li>Push-конфіги та демо-каталог</li>
          <li>Priority підтримка</li>
        </ul>
        <RouterLink to="/register" class="mt-4 inline-flex px-4 py-2 rounded-lg bg-white text-slate-900 font-semibold hover:bg-slate-200 transition">
          Обрати Growth
        </RouterLink>
      </div>
      <div class="p-6 rounded-2xl bg-slate-900/70 border border-white/5 shadow">
        <p class="text-slate-400 text-sm mb-1">Enterprise</p>
        <p class="text-3xl font-bold text-white mb-2">Custom</p>
        <ul class="space-y-2 text-sm text-slate-300">
          <li>Без лімітів пристроїв</li>
          <li>SLA 99.9% та окремий кластер</li>
          <li>SSO, SAML, аудит</li>
          <li>Виділений менеджер</li>
        </ul>
        <RouterLink to="/contact" class="mt-4 inline-flex px-4 py-2 rounded-lg bg-slate-800 text-white font-semibold hover:bg-slate-700 transition">
          Звʼязатися
        </RouterLink>
      </div>
    </div>
  </div>
</template>
