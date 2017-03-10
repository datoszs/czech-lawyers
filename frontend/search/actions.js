import {NAME} from './constants';

export const SET_QUERY = `${NAME}/SET_QUERY`;
export const ADD_ADVOCATES = `${NAME}/ADD_ADVOCATES`;
export const LOAD_MORE = `${NAME}/LOAD_MORE`;

export const setQuery = (query = '') => ({
    type: SET_QUERY,
    query,
});

export const addAdvocates = (advocates) => ({
    type: ADD_ADVOCATES,
    advocates,
});

export const loadMore = () => ({
    type: LOAD_MORE,
});
