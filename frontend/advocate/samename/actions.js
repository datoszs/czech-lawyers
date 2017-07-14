import {ACTION_PREFIX} from './constants';

export const SET_SAME_NAME_ADVOCATES = `${ACTION_PREFIX}/SET_ADVOCATES`;

export const setSameNameAdvocates = (advocates) => ({
    type: SET_SAME_NAME_ADVOCATES,
    advocates,
});
