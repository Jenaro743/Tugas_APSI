import axios from 'axios';
// Konfigurasi Axios: kirim header AJAX pada permintaan tanpa reload halaman.
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
