import querystring from 'query-string';
import {doGet} from './fetch';

const base = '/api/case';

const get = (id) => doGet(`${base}/${id}`);

const getByAdvocate = (id, court, year, result) => {
    const query = Object.entries({court, year, result})
        .filter(([, value]) => !!value)
        .reduce((queryResult, [key, value]) => Object.assign({[key]: value}, queryResult), {});
    return doGet(`/api/advocate-cases/${id}?${querystring.stringify(query)}`);
};

export default {
    get,
    getByAdvocate,
};
