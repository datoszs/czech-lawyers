import {SET_INPUT_VALUE, INITIALIZE_VALUE} from './actions';

export default (state = '', action) => (action.type === SET_INPUT_VALUE || action.type === INITIALIZE_VALUE ? action.value : state);
