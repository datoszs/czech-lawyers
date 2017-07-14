import {SET_UNAVAILABLE} from './actions';

export default (state = false, action) => (action.type === SET_UNAVAILABLE ? true : state);
