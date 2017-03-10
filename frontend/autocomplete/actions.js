import {NAME} from './constants';

export const SET_INPUT_VALUE = `${NAME}/SET_INPUT_VALUE`;

export const setInputValue = (value) => ({
    type: SET_INPUT_VALUE,
    value,
});
