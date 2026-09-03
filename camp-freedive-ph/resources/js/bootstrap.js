import axios from 'axios';
window.axios = axios;

import 'bootstrap';

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';