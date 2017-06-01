import {doGet} from './fetch';

const base = '/api/advocate';

const autocomplete = (query) => doGet(`${base}/autocomplete/${query}`);

const search = (query, start, count) => doGet(`${base}/search/${query}/${start}-${count}`);

const get = (id) => doGet(`${base}/${id}`);

const getResults = (id, court) => doGet(`/api/advocate-results/${id}${court ? `/${court}` : ''}`);

const getTopTen = () => doGet('/api/advocate-rankings/1/0-10');

const getBottomTen = () => doGet('/api/advocate-rankings/10/0-10?reverse=true');

export default {
    autocomplete,
    search,
    get,
    getResults,
    getTopTen,
    getBottomTen,
};
