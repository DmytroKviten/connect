import axios from 'axios';
window.axios = axios;

axios.defaults.withCredentials = true;   // ⬅️ ось цей рядок

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
