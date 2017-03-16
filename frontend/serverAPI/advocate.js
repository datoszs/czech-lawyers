import {doGet} from './fetch';

const base = '/api/advocate';

const autocomplete = (query) => doGet(`${base}/autocomplete/${query}`);

const search = (query, start, count) => doGet(`${base}/search/${query}/${start}-${count}`);

const get = (id) => doGet(`${base}/${id}`);

const getResults = (id, court) => doGet(`/api/advocate-results/${id}${court ? `/${court}` : ''}`);

export default {
    autocomplete,
    search,
    get,
    getResults,
};
