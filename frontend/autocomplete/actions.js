import {NAME} from './constants';

export const SET_INPUT_VALUE = `${NAME}/SET_INPUT_VALUE`;
export const SUBMIT = `${NAME}/SUBMIT`;
export const SET_AUTOCOMPLETE_RESULTS = `${NAME}/SET_AUTOCOMPLETE_RESULTS`;
export const SHOW_DROPDOWN = `${NAME}/SHOW_DROPDOWN`;
export const MOVE_SELECTION = `${NAME}/MOVE_SELECTION`;
export const SET_SELECTION = `${NAME}/SET_SELECTION`;
export const MOVE_SELECTION_INTERNAL = `${NAME}/MOVE_SELECTION/internal`;

export const submit = () => ({
    type: SUBMIT,
});

export const setInputValue = (value) => ({
    type: SET_INPUT_VALUE,
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

const moveSelection = (increment) => ({
    type: MOVE_SELECTION,
    increment,
});
export const moveSelectionUp = () => moveSelection(-1);
export const moveSelectionDown = () => moveSelection(+1);
export const setSelection = (id) => ({
    type: SET_SELECTION,
    id,
});
export const moveSelectionInternal = (increment) => ({
    type: MOVE_SELECTION_INTERNAL,
    increment,
});
