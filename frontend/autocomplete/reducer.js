import {combineReducers} from 'redux-immutable';
import {List} from 'immutable';
import {AdvocateAutocomplete} from '../model';
import {SET_ITEMS, SET_QUERY} from './actions';

const itemReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_ITEMS:
            return List(action.items.map((item) => new AdvocateAutocomplete(item)));
        case SET_QUERY:
            return action.query === '' ? List() : state;
        default:
            return state;
    }
};

const queryReducer = (state = '', action) => (action.type === SET_QUERY ? action.query : state);

export default combineReducers({
    items: itemReducer,
    query: queryReducer,
});
