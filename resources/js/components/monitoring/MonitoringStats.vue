<template>
  <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-6 py-4">
    <article :class="['monitor-card', darkMode ? 'monitor-card-dark' : 'monitor-card-light']">
      <span class="monitor-label">Average</span>
      <div class="monitor-value">
        {{ formatter(avgValue) }}
        <span>{{ powerUnitLabel }}</span>
      </div>
    </article>
    <article :class="['monitor-card', darkMode ? 'monitor-card-dark' : 'monitor-card-light']">
      <span class="monitor-label">Peak high</span>
      <div class="monitor-value">
        {{ formatter(maxPeak) }}
        <span>{{ powerUnitLabel }}</span>
      </div>
    </article>
    <article :class="['monitor-card', darkMode ? 'monitor-card-dark' : 'monitor-card-light']">
      <span class="monitor-label">Peak low</span>
      <div class="monitor-value">
        {{ formatter(minPeak) }}
        <span>{{ powerUnitLabel }}</span>
      </div>
    </article>
  </section>
</template>

<script setup>
defineProps({
  avgValue: { type: Number, default: null },
  maxPeak: { type: Number, default: null },
  minPeak: { type: Number, default: null },
  powerUnitLabel: { type: String, default: 'W' },
  darkMode: { type: Boolean, default: true },
  formatter: { type: Function, default: (v) => (v == null ? '-' : v) },
})
</script>

<style scoped>
.monitor-card {
  border-radius: 0.75rem;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  border: 1px solid;
}
.monitor-card-dark {
  background-color: rgba(15, 23, 42, 0.4);
  border-color: #1e293b;
}
.monitor-card-light {
  background-color: #ffffff;
  border-color: #e5e7eb;
}
.monitor-label {
  font-size: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.monitor-value {
  font-size: 1.5rem;
  font-weight: 600;
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}
.monitor-value span {
  font-size: 0.75rem;
}
</style>
