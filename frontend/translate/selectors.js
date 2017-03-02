import {sprintf} from 'sprintf-js';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

/**
 * Returns message and optionaly formats it.
 * @param key Message key
 * @param params Parameter object (optional)
 * @returns Formatted string
 */
export const getMessage = (state, key, params) => {
    const message = getModel(state).get(key);
    if (message) {
        if (!params) {
            return message;
        } else {
            return sprintf(message, params);
        }
    } else {
        return key; // fallback
    }
};
