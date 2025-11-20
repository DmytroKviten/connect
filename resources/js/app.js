// resources/js/app.js
import { createApp } from 'vue'
import App from './components/App.vue'
import router from './router'
import axios from 'axios'
import '../css/app.css'

// базові налаштування axios
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.baseURL = '/' // твій бек на тому ж origin

// якщо в локалстореджі є токен — підставляємо
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// глобальний перехоплювач 401
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem('token')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

const app = createApp(App)

// щоб у компонентах можна було this.$axios
app.config.globalProperties.$axios = axios

app.use(router)
app.mount('#app')
