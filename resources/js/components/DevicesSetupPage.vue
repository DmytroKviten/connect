<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-base text-white p-6">
    <!-- Step 1 -->
    <transition name="fade" mode="out-in">
      <div v-if="step === 1" key="1" class="space-y-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center">Підключення розетки</h1>
        <ol class="list-decimal list-inside text-sm text-gray-300 space-y-1">
          <li>Під’єднайтесь до Wi-Fi <code class="px-2 py-0.5 bg-black/40 rounded">shellyplug-xxxx</code>.</li>
          <li>Натисніть кнопку нижче.</li>
        </ol>

        <button
          @click="beginSetup"
          :disabled="loading"
          class="w-full py-2 rounded bg-accentDark hover:bg-accent font-semibold flex justify-center items-center"
        >
          <span v-if="!loading">Я підключився</span>
          <svg v-else class="animate-spin h-6 w-6 text-accent" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </button>

        <p v-if="uiError" class="text-sm text-red-400 text-center">{{ uiError }}</p>
        <p v-if="hint" class="text-xs text-yellow-300/90 text-center">{{ hint }}</p>
      </div>
    </transition>

    <!-- Step 2 -->
    <transition name="fade" mode="out-in">
      <div v-if="step === 2" key="2" class="space-y-4 w-full max-w-md">
        <h2 class="text-xl font-semibold text-center">Вкажіть Wi-Fi</h2>

        <input v-model="ssid" type="text" placeholder="Назва вашої мережі (SSID)"
               class="w-full px-3 py-2 rounded text-black"/>
        <input v-model="wifiPass" type="password" placeholder="Пароль до Wi-Fi"
               class="w-full px-3 py-2 rounded text-black"/>

        <div class="flex items-center justify-between text-xs text-gray-400">
          <span>AP IP:</span>
          <input v-model="apIp" type="text" class="px-2 py-1 rounded text-black w-36 text-center" />
        </div>

        <p class="text-xs text-gray-400">
          За потреби можна відкрити веб-інтерфейс розетки:
          <a class="underline" :href="`http://${apIp}`" target="_blank" rel="noreferrer">http://{{ apIp }}</a>
        </p>

        <button
          type="button"
          @click="sendConfig"
          :disabled="loading || !ssid.trim()"
          class="w-full py-2 rounded bg-accentDark hover:bg-accent font-semibold flex justify-center items-center"
        >
          <span v-if="!loading">Під’єднати</span>
          <svg v-else class="animate-spin h-6 w-6 text-accent" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </button>

        <div v-if="hint" class="text-xs text-yellow-300/90">{{ hint }}</div>
      </div>
    </transition>

    <!-- Step 3 -->
    <transition name="fade" mode="out-in">
      <div v-if="step === 3" key="3" class="space-y-6 w-full max-w-md">
        <h2 class="text-xl font-semibold text-center">Пошук пристрою у мережі…</h2>

        <div :class="['mb-2 text-center', scanStatus?.found === false ? 'text-red-500' : 'text-accent']">
          {{ scanStatus?.message || 'Зачекайте, пристрій під’єднується до вашого Wi-Fi…' }}
        </div>

        <div class="flex justify-center items-center">
          <svg class="animate-spin h-12 w-12 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        <!-- План B: ручне завершення -->
        <details class="bg-black/30 rounded-xl p-4" :open="manualOpen">
          <summary class="cursor-pointer select-none text-sm text-gray-300">
            Немає пінга? Завершити прив’язку вручну
          </summary>
          <div class="mt-3 space-y-3">
            <label class="block">
              <span class="text-xs text-gray-400">MAC (зі стікера)</span>
              <input v-model="manualMac" type="text" placeholder="AA:BB:CC:DD:EE:FF або E465B84582EC"
                     class="w-full px-3 py-2 rounded text-black"/>
            </label>
            <label class="block">
              <span class="text-xs text-gray-400">IP (необов’язково)</span>
              <input v-model="manualIp" type="text" placeholder="192.168.1.50"
                     class="w-full px-3 py-2 rounded text-black"/>
            </label>
            <button @click="finishManually" :disabled="loading"
                    class="w-full py-2 rounded bg-accentDark hover:bg-accent font-semibold">
              Завершити прив’язку
            </button>
          </div>
        </details>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

/* ---------- auth helpers ---------- */
const TOKEN_KEY = 'token'
function setAxiosAuthToken(token) {
  if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete axios.defaults.headers.common['Authorization']
}
function ensureAxios401Interceptor() {
  if (window.__AXIOS_401_INTERCEPTOR__) return
  window.__AXIOS_401_INTERCEPTOR__ = axios.interceptors.response.use(
    r => r,
    err => {
      if (err?.response?.status === 401) {
        localStorage.removeItem(TOKEN_KEY)
        setAxiosAuthToken(null)
        router.push({ path: '/login', query: { redirect: '/setup' } })
      }
      return Promise.reject(err)
    }
  )
}

/* ---------- state ---------- */
const step = ref(1)
const loading = ref(false)
const uiError = ref('')
const ssid = ref('')
const wifiPass = ref('')
const scanStatus = ref(null)
const hint = ref('')

const setupToken = ref(null)
const callbackUrl = ref(null)

const manualOpen = ref(false)
const manualMac = ref('')
const manualIp  = ref('')

const apIp = ref('192.168.33.1') // за замовчуванням для Shelly Gen2

// момент старту прив’язки — щоб визначати “свіжі” девайси
let startTs = 0

onMounted(() => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) {
    router.push({ path: '/login', query: { redirect: '/setup' } })
    return
  }
  setAxiosAuthToken(token)
  axios.defaults.withCredentials = true
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
  ensureAxios401Interceptor()
})

/* ---------- step 1: get setup token & pre-arm callbacks on AP ---------- */
async function beginSetup() {
  loading.value = true
  uiError.value = ''
  hint.value = ''
  try {
    // 1) приватний токен (auth:sanctum)
    const t = await axios.post('/api/setup-token', {}, { headers: { Accept: 'application/json' } })
    setupToken.value = t.data.token
    callbackUrl.value = t.data.callbackUrl

    // 2) Одразу пробуємо поставити callback/provisioning_url на пристрій (через AP)
    const cb = `${callbackUrl.value}?token=${encodeURIComponent(setupToken.value)}`
    await blindPost(`http://${apIp.value}/rpc/HTTP.Callback.Set`, {
      url: cb,
      method: 'POST',
      content_type: 'application/json',
      body: { token: setupToken.value, mac: '', name: 'Shelly' }
    })
    await blindPost(`http://${apIp.value}/rpc/Device.SetConfig`, {
      provisioning_url: cb
    })

    // 3) (опціонально) серверний шлях: нехай бекенд сам поговорить із AP
    // якщо контейнер бачить AP, це автоматично зробить Sys.GetStatus, WiFi.SetConfig, callback і т.д.
    // Ми викличемо це пізніше у sendConfig з SSID/паролем
    step.value = 2
    hint.value = 'Працює двома шляхами: через сервер і напряму до AP. CORS-помилки можна ігнорувати.'
  } catch (e) {
    uiError.value = e?.response?.data?.message || 'Не вдалося ініціалізувати привʼязку'
  } finally {
    loading.value = false
  }
}

/* ---------- helper: POST to device AP without CORS ---------- */
async function blindPost(url, body) {
  try {
    await fetch(url, { method: 'POST', mode: 'no-cors', body: JSON.stringify(body) })
  } catch { /* ignore */ }
}

/* ---------- step 2: send Wi-Fi config (server-first + client-fallback) ---------- */
async function sendConfig() {
  const ssidClean = ssid.value.trim()
  const passClean = wifiPass.value.trim()
  if (!ssidClean || !setupToken.value) return

  loading.value = true
  uiError.value = ''
  hint.value = ''
  startTs = Date.now()
  try {
    // 1) Спосіб сервера (якщо доступний AP із контейнера)
    try {
      await axios.post('/api/provision/claim', {
        ap_ip: apIp.value,
        ssid: ssidClean,
        password: passClean,
        token: setupToken.value
      }, { headers: { Accept: 'application/json' } })
    } catch {
      // ок, контейнер може не бачити AP — йдемо клієнтським шляхом
    }

    // 2) Спосіб клієнта напряму у AP (нечутливий до CORS)
    await blindPost(`http://${apIp.value}/rpc/WiFi.SetConfig`, {
      config: {
        sta: { ssid: ssidClean, pass: passClean, enable: true },
        ap:  { enable: false } // можемо одразу вимкнути AP
      },
      save: true
    })

    // 3) Стартуємо пошук (polling бекенду на появу пристрою, який сам зробить /api/device/ping)
    step.value = 3
    manualOpen.value = false
    scanStatus.value = { message: 'Очікую пінг від пристрою… це може зайняти до 60 секунд.' }
    pollForDevice()
  } catch {
    uiError.value = 'Не вдалося надіслати конфіг на пристрій'
  } finally {
    loading.value = false
  }
}

/* ---------- step 3: poll backend for newly claimed device ---------- */
async function pollForDevice() {
  const deadline = Date.now() + 60_000
  async function tryOnce() {
    try {
      const r = await axios.get('/api/devices', { headers: { Accept: 'application/json' } })
      const list = Array.isArray(r.data) ? r.data : (r.data?.devices ?? [])
      const found = list.find(d => {
        if (!d?.last_seen_at) return false
        const t = new Date(d.last_seen_at).getTime()
        if (Number.isNaN(t)) return false
        // вважаємо “знайдено”, якщо девайс засвітився не раніше, ніж за 2 хв від початку
        return t >= startTs - 120_000
      })
      if (found) {
        scanStatus.value = { found: true, message: 'Пристрій прив’язано! Переходимо…' }
        setTimeout(() => router.push('/devices'), 1200)
        return true
      }
    } catch {
      // ігноруємо тимчасові збої
    }
    return false
  }

  while (Date.now() < deadline) {
    if (await tryOnce()) return
    await sleep(5000)
  }

  // час вийшов — пропонуємо план Б
  manualOpen.value = true
  scanStatus.value = { found: false, message: 'Пінг затримується. Можна завершити прив’язку вручну нижче.' }
}

/* ---------- step 3 (fallback): finish manually ---------- */
async function finishManually() {
  if (!setupToken.value) return alert('Немає токена. Почніть спочатку.')
  const mac = (manualMac.value || '').toUpperCase().replace(/[^A-F0-9]/g, '')
  if (!mac) return alert('Вкажіть MAC (AA:BB:CC:DD:EE:FF або E465B84582EC)')

  loading.value = true
  try {
    await axios.post('/api/device/ping', {
      token: setupToken.value,
      mac,
      ip: (manualIp.value || '').trim() || undefined,
      name: 'Shelly Plug'
    }, { headers: { Accept: 'application/json' } })

    scanStatus.value = { found: true, message: 'Пристрій прив’язано! Переходимо…' }
    setTimeout(() => router.push('/devices'), 1200)
  } catch (e) {
    const msg = e?.response?.data?.error || e?.response?.data?.message || ''
    scanStatus.value = { found: false, message: 'Не вдалось прив’язати. ' + (msg || 'Перевір MAC/IP і спробуйте ще.') }
  } finally {
    loading.value = false
  }
}

/* ---------- utils ---------- */
function sleep(ms) { return new Promise(r => setTimeout(r, ms)) }
</script>

<style scoped>
.fade-enter-active,.fade-leave-active{transition:opacity .3s}
.fade-enter-from,.fade-leave-to{opacity:0}
.text-muted{color:#8c96a7}
.bg-accentDark{background:#129b80}
.bg-accent{background:#20e3b2}
</style>
