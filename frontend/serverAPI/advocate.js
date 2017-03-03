import {doGet} from './fetch';

const base = '/api/advocate';

const autocomplete = (query) => doGet(`${base}/autocomplete/${query}`);

const search = (query, start, count) => doGet(`${base}/search/${query}/${start}-${count}`);

const get = (id) => doGet(`${base}/${id}`);

export default {
    autocomplete,
    search,
    get,
};
