import {NAME} from './constants';

export const SET_AVAILABLE = `${NAME}/SET_AVAILABLE`;

const setAvailable = (available) => () => ({
    type: SET_AVAILABLE,
    available,
});

export const enter = setAvailable(false);
export const exit = setAvailable(true);
