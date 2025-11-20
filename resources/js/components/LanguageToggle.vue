<template>
  <div class="flex items-center rounded-full border border-white/20 bg-slate-900/40 text-xs overflow-hidden">
    <button
      v-for="opt in options"
      :key="opt.code"
      type="button"
      class="px-3 py-1 transition"
      :class="locale === opt.code ? 'bg-emerald-400 text-slate-900 font-semibold' : 'text-slate-200 hover:text-white'"
      @click="setLocale(opt.code)"
    >
      {{ opt.label }}
    </button>
  </div>
</template>

<script setup>
import useLocale from '../composables/useLocale'

const { locale, setLocale } = useLocale()

const options = [
  { code: 'uk', label: 'UA' },
  { code: 'en', label: 'EN' },
]
</script>
