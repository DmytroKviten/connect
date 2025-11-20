<template>
  <div class="min-h-screen flex flex-col text-white font-sans antialiased selection:bg-accent/30">
    <!-- HEADER -->
    <header class="fixed inset-x-0 top-0 h-16 px-6 flex items-center bg-glass/70 backdrop-blur shadow-md z-30">
      <button
        aria-label="Open the menu"
        class="p-2 mr-4 rounded hover:bg-white/10 focus-visible:outline focus-visible:outline-accent"
        @click="toggleSidebar"
      >
        <svg class="w-7 h-7 stroke-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <template v-if="sidebarOpen">
            <line x1="6" y1="6"  x2="18" y2="18"/>
            <line x1="6" y1="18" x2="18" y2="6"/>
          </template>
          <template v-else>
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </template>
        </svg>
      </button>

      <span class="flex-grow text-center text-lg font-bold tracking-wide select-none">
        CONNECT
      </span>

      <router-link to="/devices" class="ml-auto text-sm font-semibold text-white transition-colors hover:text-accent focus-visible:outline focus-visible:outline-accent">
        Connected&nbsp;devices
      </router-link>
    </header>

    <!-- SIDEBAR -->
    <aside
      class="fixed left-0 top-20 bottom-20 w-64 bg-glass backdrop-blur border-r border-white/10 rounded-r-xl p-6 transition-transform duration-300 z-40 shadow-lg"
      :class="sidebarOpen ? 'translate-x-0 pointer-events-auto' : '-translate-x-full pointer-events-none'"
    >
      <nav class="flex flex-col gap-3">
        <router-link to="/setup" class="nav-item" @click="toggleSidebar">⚙️ Налаштування</router-link>
        <router-link to="/about" class="nav-item" @click="toggleSidebar">ℹ️ Про сайт</router-link>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 pt-20">
      <section id="about" class="px-6 py-16 text-center bg-glass/40 backdrop-blur md:rounded-b-3xl shadow-xl max-w-6xl mx-auto">
        <h1 class="text-3xl md:text-5xl font-extrabold">Quick connect&nbsp;IoT device wizard</h1>
        <p class="mt-4 text-muted max-w-3xl mx-auto md:text-lg">
          Select a category, brand, and model, and the site will guide you through Wi-Fi settings and connect your device to the CONNECT cloud.
        </p>
      </section>

      <section id="setup" class="max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-8 items-start">
        <!-- селектори -->
        <div>
          <h2 class="text-2xl font-semibold mb-6">Select a device</h2>
          <div class="space-y-5">
            <!-- Category -->
            <div>
              <label class="block mb-1 text-sm text-muted" for="selCategory">Category</label>
              <select id="selCategory" class="select" v-model="selectedCategory">
                <option :value="''" disabled>— select —</option>
                <option v-for="(cat, catKey) in DATA" :key="catKey" :value="catKey">
                  {{ cat.label }}
                </option>
              </select>
            </div>
            <!-- Brand -->
            <div>
              <label class="block mb-1 text-sm text-muted" for="selBrand">Brand</label>
              <select id="selBrand" class="select" v-model="selectedBrand" :disabled="!selectedCategory">
                <option :value="''" disabled>— виберіть —</option>
                <option
                  v-for="(brand, brandKey) in selectedCategory ? DATA[selectedCategory].brands : {}"
                  :key="brandKey"
                  :value="brandKey"
                >
                  {{ brand.label }}
                </option>
              </select>
            </div>
            <!-- Model -->
            <div>
              <label class="block mb-1 text-sm text-muted" for="selModel">Model</label>
              <select id="selModel" class="select" v-model="selectedModel" :disabled="!selectedBrand">
                <option :value="''" disabled>— виберіть —</option>
                <option
                  v-for="(modelLabel, modelKey) in selectedBrand ? DATA[selectedCategory].brands[selectedBrand].models : {}"
                  :key="modelKey"
                  :value="modelKey"
                >
                  {{ modelLabel }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- фото + кнопка -->
        <div class="flex flex-col items-center text-center md:mt-10 mt-12">
          <img
            :src="imgSrc"
            :alt="imgAlt"
            class="w-72 h-72 object-contain rounded-2xl border border-white/10 shadow-lg mb-6"
            @error="onImgError"
          />

          <button
            class="btn"
            :disabled="!readyToGo || loading"
            @click="goSetup"
          >
            <span v-if="!loading">Go to the settings</span>
            <span v-else>Loading…</span>
          </button>

          <p v-if="msg" class="mt-3 text-sm text-muted">{{ msg }}</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

/* ---------- auth header (Sanctum token з /api/login) ---------- */
const TOKEN_KEY = 'token'
const token = localStorage.getItem(TOKEN_KEY)
if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

/* ---------- Дані пристроїв ---------- */
const DATA = {
  sockets: {
    label:'Sockets',
    brands:{
      shelly:{label:'Shelly',models:{'plus-plug-s':'Plus Plug S','plug-us':'Plug US'}},
      tapo  :{label:'TP-Link Tapo',models:{p100:'P100',p110:'P110'}},
      meross:{label:'Meross',models:{ms310:'MS310',mss210:'MSS210'}}
    }
  },
  lamps: {
    label:'Lamps',
    brands:{
      yeelight:{label:'Yeelight',models:{rgb:'RGB Bulb',w3:'W3 (Cool-White)'}},
      hue     :{label:'Philips Hue',models:{white:'White E27',color:'Color A60'}},
      nanoleaf:{label:'Nanoleaf',models:{a19:'A19',br30:'BR30'}}
    }
  },
  fridges:{
    label:'Fridges',
    brands:{
      samsung:{label:'Samsung',models:{familyhub:'Family Hub',bespoke:'Bespoke'}},
      lg     :{label:'LG',models:{instaview:'InstaView',thinQ:'ThinQ Smart'}}
    }
  }
}

/* ---------- state ---------- */
const selectedCategory = ref('')
const selectedBrand    = ref('')
const selectedModel    = ref('')
const sidebarOpen      = ref(false)
const loading          = ref(false)
const msg              = ref('')
const fallbackImg      = '/img/devices/placeholder.jpg'

/* ---------- UI helpers ---------- */
function toggleSidebar(){ sidebarOpen.value = !sidebarOpen.value }

const imgPath = computed(() =>
  selectedCategory.value && selectedBrand.value && selectedModel.value
    ? `/img/devices/${selectedCategory.value}-${selectedBrand.value}-${selectedModel.value}.jpg`
    : fallbackImg
)

const imgSrc = ref(imgPath.value)
const imgAlt = computed(() =>
  selectedCategory.value && selectedBrand.value && selectedModel.value
    ? `${selectedCategory.value}/${selectedBrand.value}/${selectedModel.value}`
    : 'Device preview'
)

watch(imgPath, (p) => { imgSrc.value = p })
function onImgError(){ imgSrc.value = fallbackImg }

const readyToGo = computed(() =>
  !!(selectedCategory.value && selectedBrand.value && selectedModel.value)
)

/* ---------- create setup token & go ---------- */
async function goSetup(){
  msg.value = ''
  if (!readyToGo.value) return
  if (!token) {
    router.push({ path: '/login', query: { redirect: location.pathname } })
    return
  }
  loading.value = true
  try {
    const r = await axios.post('/api/setup-token', {}, { headers: { Accept:'application/json' } })
    const tk = r.data?.token
    if (!tk) throw new Error('No token from server')

    // передамо вибір у query — сторінка /setup зможе одразу викликати ProvisionController::claim
    const q = new URLSearchParams({
      category: selectedCategory.value,
      brand: selectedBrand.value,
      model: selectedModel.value,
      token: tk
    }).toString()

    router.push(`/setup?${q}`)
  } catch(e){
    msg.value = 'Не вдалося створити токен налаштування. Увійдіть у профіль і спробуйте знову.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
body::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  background:
    radial-gradient(circle at 30% 10%, rgba(32,227,178,.25), transparent 60%),
    radial-gradient(circle at 70% 90%, rgba(32,227,178,.15), transparent 60%),
    #0e0f11;
}
.text-muted { color: #8c96a7; }
.nav-item {
  display: block;
  padding: .75rem 1rem;
  border-radius: .5rem;
  transition: .2s background-color;
}
.nav-item:hover,
.nav-item:focus-visible { background-color: rgba(32,227,178,.1); }
.select {
  width: 100%;
  color: #fff;
  font-size: 1rem;
  line-height: 1.5rem;
  background-color: #1b1d22;
  border: 2px solid rgba(32,227,178,.4);
  padding: .5rem 2.5rem .5rem 1rem;
  border-radius: .75rem;
  transition: border-color .2s,box-shadow .2s;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: .9rem;
}
.select:focus {
  border-color: #20e3b2;
  outline: none;
  box-shadow: 0 0 0 2px rgba(32,227,178,.45);
}
.select option { background: #1b1d22; color: #e5e7eb; }
.select option:disabled { color: #6b7280; }
.btn {
  display: inline-flex;
  align-items: center;
  gap: .25rem;
  padding: .75rem 1.5rem;
  font-weight: 600;
  border-radius: 9999px;
  background: #20e3b2;
  color: #0e0f11;
  box-shadow: 0 4px 18px rgba(32,227,178,.2);
  transition: .2s background-color;
}
.btn:hover { background: #129b80; }
.btn:disabled { opacity: .5; pointer-events: none; }
</style>
