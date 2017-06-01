import {NAME as PARENT_NAME} from '../constants';
import {NAME} from './constants';

export const SET_RESULT = `${PARENT_NAME}/${NAME}/SET_RESULT`;
export const SET_CASE = `${PARENT_NAME}/${NAME}/CASE_ID`;

export const setResult = (result) => ({
    type: SET_RESULT,
    result,
});

export const setCase = (caseId) => ({
    type: SET_CASE,
    caseId,
});
