import {NAME} from './constants';

export const SET_ID = `${NAME}/SET_ID`;
export const SET_ADVOCATE = `${NAME}/SET_ADVOCATE`;
export const SET_RESULTS = `${NAME}/SET_RESULTS`;

export const setId = (id) => ({
    type: SET_ID,
    id,
});

export const setAdvocate = (advocate) => ({
    type: SET_ADVOCATE,
    advocate,
});

export const setResults = (results) => ({
    type: SET_RESULTS,
    results,
});