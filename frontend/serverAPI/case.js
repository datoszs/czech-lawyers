import {doGet} from './fetch';

const base = '/api/case';

const get = (id) => doGet(`${base}/${id}`);

export default {
    get,
};
