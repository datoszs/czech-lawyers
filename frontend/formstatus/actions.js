import {NAME} from './constants';

export const SUCCESS = `${NAME}/SUCCESS`;
export const ERROR = `${NAME}/ERROR`;
export const START_SUBMIT = `${NAME}/START_SUBMIT`;
export const CLEAR_SUCCESS = `${NAME}/CLEAR_SUCCESS`;
export const CLEAR_ERROR = `${NAME}/CLEAR_ERROR`;

export const setSuccess = (formName) => ({
    type: SUCCESS,
    formName,
});
export const setError = (formName, error) => ({
    type: ERROR,
    formName,
    error,
});
export const startSubmit = (formName) => ({
    type: START_SUBMIT,
    formName,
});
export const clearSuccess = (formName) => ({
    type: CLEAR_SUCCESS,
    formName,
});
export const clearError = (formName) => ({
    type: CLEAR_ERROR,
    formName,
});
