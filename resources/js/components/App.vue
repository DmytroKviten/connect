<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 overflow-x-hidden">
    <MainHeader v-if="!isAuthRoute" />
    <main class="pt-16">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import MainHeader from './MainHeader.vue'

const route = useRoute()
const isAuthRoute = computed(() => route.path === '/login' || route.path === '/register')
</script>
