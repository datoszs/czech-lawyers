import {SET_INPUT_VALUE} from './actions';

export default (state = '', action) => (action.type === SET_INPUT_VALUE ? action.value : state);
