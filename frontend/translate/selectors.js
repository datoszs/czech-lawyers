import {sprintf} from 'sprintf-js';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

/**
 * Returns message and optionally formats it.
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

/**
 * Returns short date format (e.g. 31. 3. 2017 or 3/31/2017).
 * @param state
 */
export const getShortDateFormat = () => 'D. M. Y';
