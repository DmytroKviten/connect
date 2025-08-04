import { createApp } from 'vue'
import HomePage from './components/HomePage.vue'
import DemoPage from './components/DemoPage.vue'

const app = createApp({})

app.component('home-page', HomePage)
app.component('demo-page', DemoPage)

app.mount('#app')

