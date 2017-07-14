import {SET_AVAILABLE} from './actions';

export default (state = true, action) => (action.type === SET_AVAILABLE ? action.available : state);
