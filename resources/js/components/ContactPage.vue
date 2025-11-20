<template>
  <div class="max-w-4xl mx-auto px-4 md:px-6 py-12">
    <div class="bg-slate-900/70 border border-white/5 rounded-2xl p-8 shadow-xl space-y-6">
      <div>
        <p class="text-emerald-300 text-xs font-semibold uppercase tracking-[0.2em] mb-2">Get in touch</p>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Напишіть нам — відповімо протягом 1 робочого дня</h1>
        <p class="text-slate-300">Питання щодо інтеграції, ціноутворення чи партнерства? Ми на звʼязку.</p>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm text-slate-300">Ваш email</label>
          <input type="email" placeholder="you@company.com" class="w-full px-3 py-2 rounded-lg bg-slate-800/70 border border-slate-700 text-sm text-white focus:outline-none focus:border-emerald-400" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-slate-300">Тема</label>
          <input type="text" placeholder="Наприклад: інтеграція через API" class="w-full px-3 py-2 rounded-lg bg-slate-800/70 border border-slate-700 text-sm text-white focus:outline-none focus:border-emerald-400" />
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm text-slate-300">Повідомлення</label>
        <textarea rows="4" placeholder="Розкажіть коротко про задачу" class="w-full px-3 py-2 rounded-lg bg-slate-800/70 border border-slate-700 text-sm text-white focus:outline-none focus:border-emerald-400"></textarea>
      </div>

      <div class="flex flex-wrap gap-3 items-center">
        <button class="px-4 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400 transition">Надіслати</button>
        <span class="text-xs text-slate-400">Або напишіть напряму: support@smartpower.dev</span>
      </div>
    </div>
  </div>
</template>
