<!-- resources/js/components/LoginPage.vue -->
<template>
  <div class="page">
    <canvas ref="stars" class="bg"></canvas>

    <form class="card" @submit.prevent="login" novalidate>
      <h2 class="title">Вхід</h2>

      <p v-if="error" class="error">{{ error }}</p>

      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="input"
        required
        autocomplete="username"
        autofocus
      />
      <input
        v-model="password"
        type="password"
        placeholder="Пароль"
        class="input"
        required
        minlength="6"
        autocomplete="current-password"
      />

      <button class="btn" :disabled="loading">
        <span v-if="!loading">Увійти</span>
        <span v-else>Завантаження...</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const stars = ref(null)

const router = useRouter()
const route  = useRoute()

let animationId = 0
let resizeHandler = null

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

// безпечне визначення, куди повертати після логіну
function getRedirectPath() {
  const q = route.query?.redirect
  return (typeof q === 'string' && q.startsWith('/')) ? q : '/devices'
}

onMounted(() => {
  applyAxiosDefaults()

  // якщо вже залогінений — одразу кидаємо на redirect або /devices
  const savedToken = localStorage.getItem(TOKEN_KEY)
  if (savedToken) {
    setAxiosAuthToken(savedToken)
    router.replace(getRedirectPath())
    return
  }

  // зоряний фон
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
      velocity: Math.random() * 0.5 + 0.2,
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

async function login() {
  loading.value = true
  error.value = ''

  try {
    const res = await axios.post(
      '/api/login',
      { email: email.value.trim(), password: password.value },
      { headers: { Accept: 'application/json' } }
    )

    const token = res?.data?.token
    if (!token) throw new Error('Сервер не повернув токен')

    // Зберігаємо токен і підставляємо в axios
    localStorage.setItem(TOKEN_KEY, token)
    setAxiosAuthToken(token)

    // Кладемо ім'я користувача (якщо сервер повертає)
    const nameFromLogin = res?.data?.user?.name
    if (nameFromLogin) {
      localStorage.setItem(USERNAME_KEY, nameFromLogin)
    } else {
      // фолбек: підтягнути ім'я з /api/user (не критично, але приємно)
      try {
        const u = await axios.get('/api/user', { headers: { Accept: 'application/json' } })
        if (u?.data?.name) localStorage.setItem(USERNAME_KEY, u.data.name)
      } catch { /* ignore */ }
    }

    // редіректимо туди, звідки прийшли або на /devices
    router.replace(getRedirectPath())
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.response?.data?.errors?.email?.[0] ||
      e?.response?.data?.errors?.password?.[0] ||
      'Неправильні дані або помилка'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page {
  position: relative;
  min-height: 100vh;
  background: radial-gradient(ellipse at bottom, #1b2735 0%, #090a0f 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  display: block;
}
.card {
  position: relative;
  z-index: 1;
  width: 22rem;
  padding: 2rem;
  border-radius: 14px;
  background: #fff;
  color: #111;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
}
.title {
  font-size: 1.5rem;
  font-weight: 800;
  text-align: center;
  margin-bottom: 1rem;
}
.error {
  color: #dc2626;
  margin-bottom: 0.75rem;
  text-align: center;
}
.input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 1rem;
}
.btn {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 0;
  border-radius: 0.6rem;
  font-weight: 700;
  color: white;
  background: #2563eb;
  cursor: pointer;
}
.btn:disabled {
  opacity: 0.6;
  cursor: default;
}
</style>


