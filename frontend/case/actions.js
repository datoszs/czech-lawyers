import {NAME} from './constants';

export const SET_ID = `${NAME}/SET_ID`;
export const SET_DETAIL = `${NAME}/SET_DETAIL`;
export const OPEN_DISPUTE_FORM = `${NAME}/OPEN_DISPUTE_FORM`;
export const DISPUTE = `${NAME}/DISPUTE`;

export const setId = (id) => ({
    type: SET_ID,
    id,
});

export const setDetail = (detail) => ({
    type: SET_DETAIL,
    detail,
});

export const openDisputeForm = () => ({
    type: OPEN_DISPUTE_FORM,
});

export const dispute = (values) => ({
    type: DISPUTE,
    values,
});
