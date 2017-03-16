import querystring from 'query-string';
import {doGet} from './fetch';

const base = '/api/case';

const get = (id) => doGet(`${base}/${id}`);

const getByAdvocate = (id, {court, year, result}) => doGet(`/api/advocate-cases/${id}?${querystring.stringify({court, year, result})}`);

export default {
    get,
    getByAdvocate,
};
