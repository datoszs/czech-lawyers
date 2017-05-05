import {NAME} from './constants';

export const SET_INPUT_VALUE = `${NAME}/SET_INPUT_VALUE`;
export const INITIALIZE_VALUE = `${NAME}/INITIALIZE_VALUE`;
export const SUBMIT = `${NAME}/SUBMIT`;
export const SET_AUTOCOMPLETE_RESULTS = `${NAME}/SET_AUTOCOMPLETE_RESULTS`;
export const SHOW_DROPDOWN = `${NAME}/SHOW_DROPDOWN`;

export const submit = () => ({
    type: SUBMIT,
});

export const setInputValue = (value) => ({
    type: SET_INPUT_VALUE,
    value,
});

export const initializeValue = (value = '') => ({
    type: INITIALIZE_VALUE,
    value,
});

export const setAutocompleteResults = (results) => ({
    type: SET_AUTOCOMPLETE_RESULTS,
    results,
});

export const hideDropdown = () => setAutocompleteResults([]);

export const showDropdown = () => ({
    type: SHOW_DROPDOWN,
});
