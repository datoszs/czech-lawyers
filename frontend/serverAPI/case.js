import querystring from 'query-string';
import {doGet, doPost} from './fetch';

const base = '/api/case';

const search = (query, start, count) => doGet(`${base}/search/${query}/${start}-${count}`);

const get = (id) => doGet(`${base}/${id}`);

const getByAdvocate = (id, court, year, result) => {
    const query = Object.entries({court, year, result})
        .filter(([, value]) => !!value)
        .reduce((queryResult, [key, value]) => Object.assign({[key]: value}, queryResult), {});
    return doGet(`/api/advocate-cases/${id}?${querystring.stringify(query)}`);
};

const dispute = (id, values) => doPost(`/api/dispute-case/${id}`)(values);

export default {
    search,
    get,
    getByAdvocate,
    dispute,
};
