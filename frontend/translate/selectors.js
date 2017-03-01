import {sprintf} from 'sprintf-js';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

/**
 * Returns message and optionaly formats it.
 * @param key Message key
 * @param parameters Parameter object (optional)
 * @returns Formatted string
 */
export const getMessage = (state, key, parameters) => {
    const message = getModel(state).get(key);
    if (message) {
        if (!parameters) {
            return message;
        } else {
            return sprintf(message, parameters);
        }
    } else {
        return key; // fallback
    }
};
