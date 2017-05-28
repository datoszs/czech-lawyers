import {NAME} from './constants';

export const SUCCESS = `${NAME}/SUCCESS`;
export const ERROR = `${NAME}/ERROR`;
export const CLEAR = `${NAME}/CLEAR`;

export const setSuccess = (formName) => ({
    type: SUCCESS,
    formName,
});
export const setError = (formName, error) => ({
    type: ERROR,
    formName,
    error,
});
export const clear = (formName) => ({
    type: CLEAR,
    formName,
});
