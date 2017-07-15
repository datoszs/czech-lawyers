import {NAME} from './constants';

export const SET_UNAVAILABLE = `${NAME}/SET_UNAVAILABLE`;

export const enter = () => ({
    type: SET_UNAVAILABLE,
});
