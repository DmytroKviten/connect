<template>
  <aside
    :class="[
      'w-64 border-r px-4 py-5 overflow-y-auto transition-colors space-y-4',
      darkMode ? 'border-slate-800 bg-slate-900/60' : 'border-slate-200 bg-white/90'
    ]"
  >
    <div class="flex items-center justify-between">
      <h2 :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">
        Devices
      </h2>
      <span
        :class="[
          'w-1.5 h-1.5 rounded-full animate-pulse',
          onlineCount > 0 ? 'bg-emerald-400' : 'bg-slate-400'
        ]"
      ></span>
    </div>

    <button
      class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition"
      :class="allSelected
        ? (darkMode ? 'bg-slate-200 text-slate-900 shadow-sm' : 'bg-slate-900 text-white shadow-sm')
        : (darkMode ? 'bg-slate-800/40 text-slate-200 hover:bg-slate-800/70' : 'bg-slate-100 text-slate-700 hover:bg-slate-200')"
      @click="$emit('select-all')"
    >
      <span>All devices</span>
      <span class="text-[11px] px-2 py-0.5 rounded-md border border-white/10">{{ total }}</span>
    </button>

    <div v-if="items.length">
      <button
        v-for="item in items"
        :key="item.id"
        class="w-full px-3 py-2 rounded-lg mb-2 text-sm flex items-center justify-between gap-2 transition"
        :class="item.selected
          ? (darkMode ? 'bg-slate-200 text-slate-900 shadow-sm' : 'bg-slate-900 text-white shadow-sm')
          : (darkMode ? 'bg-slate-800/20 text-slate-200 hover:bg-slate-800/50' : 'bg-slate-100 text-slate-700 hover:bg-slate-200')"
        @click="$emit('toggle', item.id)"
      >
        <div class="flex flex-col gap-0 leading-tight text-left">
          <span class="truncate">{{ item.name }}</span>
          <span class="text-[10px]" :class="darkMode ? 'text-slate-400' : 'text-slate-500'" v-if="item.avgLabel || item.lastSeenLabel">
            <template v-if="item.avgLabel">{{ item.avgLabel }}</template>
            <template v-if="item.avgLabel && item.lastSeenLabel"> · </template>
            <template v-if="item.lastSeenLabel">{{ item.lastSeenLabel }}</template>
          </span>
        </div>
        <div class="flex items-center gap-1">
          <span
            :class="[
              'w-2 h-2 rounded-full',
              item.isOnline ? 'bg-emerald-400' : 'bg-rose-400'
            ]"
          ></span>
          <span
            v-if="item.selected"
            :class="[
              'text-[10px] uppercase tracking-wide px-2 py-0.5 rounded border',
              darkMode ? 'bg-slate-900/20 border-slate-700' : 'bg-white/70 border-slate-200'
            ]"
          >
            ON
          </span>
        </div>
      </button>
    </div>
    <div v-else class="text-xs text-slate-500">
      No devices yet.
    </div>
  </aside>
</template>

<script setup>
defineProps({
  darkMode: { type: Boolean, default: true },
  items: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  allSelected: { type: Boolean, default: true },
  onlineCount: { type: Number, default: 0 },
})

defineEmits(['select-all', 'toggle'])
</script>
