import router from '../router';
import {ADVOCATE_SEARCH, ADVOCATE_DETAIL} from '../routes';
import {NAME} from './constants';

export const SET_QUERY = `${NAME}/SET_QUERY`;
export const SET_ITEMS = `${NAME}/SET_ITEMS`;

export const setQuery = (query) => ({
    type: SET_QUERY,
    query,
});

export const setItems = (items) => ({
    type: SET_ITEMS,
    items,
});

export const goToSearch = (query) => router.transition(ADVOCATE_SEARCH, undefined, {query});
export const goToAdvocate = (id) => router.transition(ADVOCATE_DETAIL, {id});
