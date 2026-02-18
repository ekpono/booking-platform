import axios from 'axios';

let csrfPromise = null;

export const ensureCsrfCookie = () => {
    if (!csrfPromise) {
        csrfPromise = axios.get('/sanctum/csrf-cookie');
    }

    return csrfPromise;
};
