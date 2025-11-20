<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 z-0">
      <canvas ref="stars" class="w-full h-full"></canvas>
    </div>

    <form @submit.prevent="register" class="z-10 bg-white p-8 rounded shadow w-96 text-black" novalidate>
      <h2 class="text-2xl font-bold mb-6 text-center">Реєстрація</h2>

      <div v-if="error" class="mb-4 text-red-600">{{ error }}</div>

      <input
        v-model="name"
        type="text"
        placeholder="Ім’я"
        class="w-full p-2 border rounded mb-4"
        required
        autocomplete="name"
        autofocus
      />
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="w-full p-2 border rounded mb-4"
        required
        autocomplete="email"
      />
      <input
        v-model="password"
        type="password"
        placeholder="Пароль"
        class="w-full p-2 border rounded mb-4"
        required
        minlength="6"
        autocomplete="new-password"
      />
      <input
        v-model="password_confirmation"
        type="password"
        placeholder="Підтвердження пароля"
        class="w-full p-2 border rounded mb-4"
        required
        minlength="6"
        autocomplete="new-password"
      />

      <button :disabled="loading" type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 disabled:opacity-60">
        <span v-if="!loading">Зареєструватися</span>
        <span v-else>...</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const error = ref('')
const stars = ref(null)

const router = useRouter()
const route  = useRoute()

// ---- axios / auth helpers ----
const TOKEN_KEY = 'token'
const USERNAME_KEY = 'userName'

function applyAxiosDefaults() {
  axios.defaults.withCredentials = true
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
}
function setAxiosAuthToken(token) {
  if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete axios.defaults.headers.common['Authorization']
}
function getRedirectPath() {
  const q = route.query?.redirect
  return (typeof q === 'string' && q.startsWith('/')) ? q : '/devices'
}

// ---- star background ----
let resizeHandler = null
let animationId = 0

onMounted(() => {
  applyAxiosDefaults()

  // якщо вже залогінений — одразу на redirect або /devices
  const savedToken = localStorage.getItem(TOKEN_KEY)
  if (savedToken) {
    setAxiosAuthToken(savedToken)
    router.replace(getRedirectPath())
    return
  }

  const canvas = stars.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  let width, height
  const starsArr = []

  resizeHandler = () => {
    width = canvas.width = window.innerWidth
    height = canvas.height = window.innerHeight
  }
  window.addEventListener('resize', resizeHandler, { passive: true })
  resizeHandler()

  for (let i = 0; i < 200; i++) {
    starsArr.push({
      x: Math.random() * width,
      y: Math.random() * height,
      radius: Math.random() * 1.5,
      velocity: Math.random() * 0.5 + 0.2
    })
  }

  const animate = () => {
    ctx.clearRect(0, 0, width, height)
    for (const star of starsArr) {
      ctx.beginPath()
      ctx.arc(star.x, star.y, star.radius, 0, 2 * Math.PI)
      ctx.fillStyle = 'white'
      ctx.fill()
      star.y += star.velocity
      if (star.y > height) {
        star.y = 0
        star.x = Math.random() * width
      }
    }
    animationId = requestAnimationFrame(animate)
  }
  animate()
})

onUnmounted(() => {
  if (resizeHandler) window.removeEventListener('resize', resizeHandler)
  if (animationId) cancelAnimationFrame(animationId)
})

// ---- submit ----
async function register() {
  loading.value = true
  error.value = ''

  // простий клієнтський чек перед запитом
  if (password.value.length < 6) {
    error.value = 'Пароль має містити щонайменше 6 символів'
    loading.value = false
    return
  }
  if (password.value !== password_confirmation.value) {
    error.value = 'Паролі не співпадають'
    loading.value = false
    return
  }

  try {
    const res = await axios.post(
      '/api/register',
      {
        name: name.value.trim(),
        email: email.value.trim(),
        password: password.value,
        password_confirmation: password_confirmation.value,
      },
      { headers: { Accept: 'application/json' } }
    )

    const token = res?.data?.token
    if (!token) throw new Error('Сервер не повернув токен')

    // зберігаємо токен та імʼя користувача
    localStorage.setItem(TOKEN_KEY, token)
    localStorage.setItem(USERNAME_KEY, res?.data?.user?.name || name.value.trim())
    setAxiosAuthToken(token)

    router.replace(getRedirectPath())
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.response?.data?.errors?.email?.[0] ||
      e?.response?.data?.errors?.password?.[0] ||
      'Помилка реєстрації'
  } finally {
    loading.value = false
  }
}
</script>


