import {combineReducers} from 'redux-immutable';
import {SET_QUERY} from './actions';

const queryReducer = (state = '', action) => (action.type === SET_QUERY ? action.query : state);

const reducer = combineReducers({
    query: queryReducer,
});

export default (state, action) => {
    if (action.type === SET_QUERY && state && state.get('query') === action.query) {
        return state;
    } else {
        return reducer(state, action);
    }
};
