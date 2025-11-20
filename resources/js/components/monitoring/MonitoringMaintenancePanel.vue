<template>
  <div :class="['rounded-2xl border p-4', darkMode ? 'border-slate-800 bg-slate-900/40' : 'border-slate-200 bg-white']">
    <p class="text-[11px] uppercase tracking-[0.18em]" :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Maintenance</p>
    <h3 class="text-lg font-semibold mb-2" :class="darkMode ? 'text-white' : 'text-slate-900'">Planned downtime</h3>
    <form class="space-y-3" @submit.prevent="$emit('add-slot')">
      <div>
        <label class="text-xs uppercase tracking-wide" :class="darkMode ? 'text-slate-500' : 'text-slate-500'">Start</label>
        <input type="datetime-local" v-model="newSlot.start" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm bg-transparent"
          :class="darkMode ? 'border-slate-700 text-slate-100' : 'border-slate-300'" />
      </div>
      <div>
        <label class="text-xs uppercase tracking-wide" :class="darkMode ? 'text-slate-500' : 'text-slate-500'">Notes</label>
        <input type="text" v-model.trim="newSlot.note" placeholder="Routine check, patching, etc."
          class="mt-1 w-full rounded-lg border px-3 py-2 text-sm bg-transparent"
          :class="darkMode ? 'border-slate-700 text-slate-100 placeholder:text-slate-500' : 'border-slate-300 placeholder:text-slate-400'" />
      </div>
      <button
        type="submit"
        class="w-full px-3 py-2 rounded-lg text-sm font-semibold border transition"
        :disabled="!newSlot.start || !newSlot.note"
        :class="[
          !newSlot.start || !newSlot.note
            ? 'opacity-60 cursor-not-allowed'
            : darkMode ? 'border-emerald-500 text-emerald-300 hover:bg-emerald-500/10' : 'border-emerald-500 text-emerald-600 hover:bg-emerald-50'
        ]"
      >
        Save slot
      </button>
    </form>
    <div class="mt-4 space-y-2" v-if="plannedSlots.length">
      <div
        v-for="slot in plannedSlots"
        :key="slot.id"
        class="flex items-start justify-between gap-3 text-sm"
      >
        <div>
          <p :class="darkMode ? 'text-slate-100' : 'text-slate-800'">{{ humanTime(slot.start) }}</p>
          <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'">{{ slot.note }}</p>
        </div>
        <span class="text-[11px] px-2 py-0.5 rounded-full border" :class="darkMode ? 'border-slate-700 text-slate-400' : 'border-slate-200 text-slate-500'">Upcoming</span>
      </div>
    </div>
    <p v-else class="text-sm" :class="darkMode ? 'text-slate-500' : 'text-slate-500'">No maintenance slots planned.</p>
  </div>
</template>

<script setup>
defineProps({
  newSlot: { type: Object, required: true },
  plannedSlots: { type: Array, default: () => [] },
  humanTime: { type: Function, required: true },
  darkMode: { type: Boolean, default: true },
})

defineEmits(['add-slot'])
</script>
