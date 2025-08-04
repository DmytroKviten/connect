<template>
  <div>
    <!-- Header -->
    <header id="main-header" :class="headerClass">
      <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <img :src="logoSrc" alt="Connect Logo" class="h-16 w-16" />
          <h1 class="text-2xl font-bold text-white">CONNECT</h1>
        </div>
        <nav class="hidden md:block">
          <ul class="flex space-x-6">
            <li>
              <a href="#" class="text-white text-2xl hover:text-blue-300 font-medium">HOME</a>
            </li>
            <li>
              <a href="#features" class="text-white text-2xl hover:text-blue-300 font-medium">PRODUCTS</a>
            </li>
            <li>
              <a href="#demo" class="text-white text-2xl hover:text-blue-300 font-medium">COMPANY</a>
            </li>
            <li>
              <a href="#contact" class="text-white text-2xl hover:text-blue-300 font-medium">SERVICES</a>
            </li>
          </ul>
        </nav>
        <div class="flex items-center space-x-4">
          <template v-if="isAuth">
            <a href="/demo" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
              DEMO
            </a>
            <span class="text-white font-semibold">{{ userName }}</span>
            <form id="logout-form" action="/logout" method="POST" class="hidden">
              <input type="hidden" name="_token" :value="csrfToken" />
            </form>
            <a href="#" @click.prevent="logout" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
              Вийти
            </a>
          </template>
          <template v-else>
            <a id="login-btn" href="/login" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
              LOG&nbsp;IN
            </a>
            <a href="/register" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
              REGISTER
            </a>
          </template>
        </div>
      </div>
    </header>

    <!-- Hero -->
    <section class="relative min-h-screen overflow-hidden">
      <div class="absolute inset-0 z-0">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
          <source :src="videoSrc" type="video/mp4" />
          Ваш браузер не підтримує відео.
        </video>
        <div class="absolute inset-0 bg-black/75"></div>
      </div>
      <div class="relative z-10 flex flex-col items-center justify-center min-h-screen text-center px-4">
        <h1 class="text-5xl md:text-6xl font-bold text-white mt-8 mb-4">CONNECT</h1>
        <h2 class="text-2xl md:text-4xl text-white mt-4 mb-6">
          Revolutionize Your IoT Ecosystem
        </h2>
        <p class="max-w-xl md:text-2xl text-white mt-4 mb-8">
          Experience unparalleled device integration, actionable analytics, and intuitive visualization —
          all designed to empower your digital transformation and drive innovation.
        </p>
        <a href="#features" class="px-8 py-4 bg-blue-600 text-white rounded-full font-semibold hover:bg-blue-700 transition">
          Learn More
        </a>
      </div>
    </section>

    <!-- Solutions -->
    <section id="features" class="py-20 bg-gray-100">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-12">Our Solutions</h2>
        <div class="grid gap-8 md:grid-cols-3">
          <div class="bg-white p-6 rounded-lg shadow hover:shadow-xl transition">
            <svg class="h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 7l6 6-6 6M21 7l-6 6 6 6"/></svg>
            <h3 class="text-xl font-semibold mb-2">Device On-Boarding</h3>
            <p class="text-gray-600 mb-4">Zero-touch provisioning and secure key exchange in under 60 seconds.</p>
            <a href="#" class="text-blue-600 font-medium hover:underline">Learn more →</a>
          </div>
          <div class="bg-white p-6 rounded-lg shadow hover:shadow-xl transition">
            <svg class="h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/></svg>
            <h3 class="text-xl font-semibold mb-2">Real-time Analytics</h3>
            <p class="text-gray-600 mb-4">Actionable insights and anomaly detection with sub-second latency.</p>
            <a href="#" class="text-blue-600 font-medium hover:underline">Learn more →</a>
          </div>
          <div class="bg-white p-6 rounded-lg shadow hover:shadow-xl transition">
            <svg class="h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 6v6l4 2"/></svg>
            <h3 class="text-xl font-semibold mb-2">Edge Automation</h3>
            <p class="text-gray-600 mb-4">Push rules to edge devices & keep control even without internet.</p>
            <a href="#" class="text-blue-600 font-medium hover:underline">Learn more →</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section id="why-us" class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose Connect?</h2>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
          <div class="p-6 bg-gray-100 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="text-xl font-semibold mb-2">99.9 % uptime</h3>
            <p class="text-gray-600">Automatic fail-over keeps dashboards and devices online during any outage.</p>
          </div>
          <div class="p-6 bg-gray-100 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="text-xl font-semibold mb-2">&lt; 5 s latency</h3>
            <p class="text-gray-600">Global edge network delivers telemetry in under five seconds.</p>
          </div>
          <div class="p-6 bg-gray-100 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="text-xl font-semibold mb-2">GDPR compliant</h3>
            <p class="text-gray-600">EU-hosted, AES-256 encryption, full right-to-erasure on request.</p>
          </div>
          <div class="p-6 bg-gray-100 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="text-xl font-semibold mb-2">24/7 support</h3>
            <p class="text-gray-600">Senior engineers on call with &lt; 15 min first response for critical tickets.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="cta" class="py-16 bg-gradient-to-r from-blue-700 to-blue-900 text-white">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-4">Ready to Transform Your Business?</h2>
        <p class="text-xl mb-8">Join us now and take your IoT solutions to the next level.</p>
        <a href="/register" class="px-8 py-4 bg-green-600 text-white rounded-full font-semibold hover:bg-green-700 transition">
          Get Started
        </a>
      </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">What Our Clients Say</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition">
            <p class="text-gray-700 italic mb-4">“This platform revolutionized our IoT strategy.”</p>
            <h3 class="text-xl font-semibold text-gray-800">John Doe</h3>
            <p class="text-gray-600">CEO, TechCorp</p>
          </div>
          <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition">
            <p class="text-gray-700 italic mb-4">“A game-changer in device management.”</p>
            <h3 class="text-xl font-semibold text-gray-800">Jane Smith</h3>
            <p class="text-gray-600">CTO, Innovate Inc.</p>
          </div>
          <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition">
            <p class="text-gray-700 italic mb-4">“Comprehensive features make IoT management seamless.”</p>
            <h3 class="text-xl font-semibold text-gray-800">Alex Johnson</h3>
            <p class="text-gray-600">COO, Smart Solutions</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-800 text-gray-300 py-8">
      <div class="container mx-auto px-4 text-center">
        <p>&copy; {{ new Date().getFullYear() }} Connect. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  isAuth: Boolean,
  userName: String,
})

const logoSrc = '/home/image/krest.png'
const videoSrc = '/home/video/videofon.mp4'

// csrf для logout
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

const scrolled = ref(false)
const headerClass = computed(() =>
  [
    "fixed w-full top-0 z-50 transition-colors duration-300",
    scrolled.value
      ? "bg-blue-600/90 backdrop-blur-lg shadow"
      : "bg-transparent",
  ].join(' ')
)

onMounted(() => {
  window.addEventListener('scroll', () => {
    scrolled.value = window.scrollY > 60
  })
})

function logout() {
  document.getElementById('logout-form').submit()
}
</script>
