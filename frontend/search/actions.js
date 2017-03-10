import {NAME} from './constants';

export const SET_QUERY = `${NAME}/SET_QUERY`;

export const setQuery = (query) => ({
    type: SET_QUERY,
    query,
});
