import {NAME} from './constants';

export const SET_ID = `${NAME}/SET_ID`;
export const SET_DETAIL = `${NAME}/SET_DETAIL`;

export const setId = (id) => ({
    type: SET_ID,
    id,
});

export const setDetail = (detail) => ({
    type: SET_DETAIL,
    detail,
});
