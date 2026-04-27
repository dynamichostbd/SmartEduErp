import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios.interceptors.response.use(
    (response) => {
        const data = response?.data;
        if (typeof data === 'string') {
            const s = data.trim().toLowerCase();
            if (s.includes('<!doctype html') || s.includes('<html')) {
                const base = window.laravel?.baseurl || '';
                window.location = `${base}/admin/loginme`;
                return Promise.reject(new Error('Session expired.'));
            }
        }
        return response;
    },
    (error) => {
        const data = error?.response?.data;
        if (typeof data === 'string') {
            const s = data.trim().toLowerCase();
            if (s.includes('<!doctype html') || s.includes('<html')) {
                const base = window.laravel?.baseurl || '';
                window.location = `${base}/admin/loginme`;
            }
        }
        return Promise.reject(error);
    }
);
