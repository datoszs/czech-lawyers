import {combineReducers} from 'redux-immutable';
import {List, Map} from 'immutable';
import {Advocate} from '../model';
import {search} from './import';
import {LIMIT, LIMIT_INCREMENT, PAGE_SIZE} from './constants';
import {SET_QUERY, ADD_ADVOCATES, LOAD_MORE} from './actions';

const queryReducer = (state = '', action) => (action.type === SET_QUERY ? action.query : state);

const advocateListReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_QUERY:
            return List();
        case ADD_ADVOCATES:
            return state.concat(action.advocates.map(({id}) => id));
        default:
            return state;
    }
};

const advocateReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_QUERY:
            return Map();
        case ADD_ADVOCATES:
            return state.merge(Map(action.advocates.map((advocate) => [advocate.id, new Advocate(advocate)])));
        default:
            return state;
    }
};

const limitReducer = (state = LIMIT, action) => {
    switch (action.type) {
        case SET_QUERY:
            return LIMIT;
        case LOAD_MORE:
            return state + LIMIT_INCREMENT;
        default:
            return state;
    }
};

const finishedReducer = (state = true, action) => {
    switch (action.type) {
        case SET_QUERY:
            return action.query === '';
        case ADD_ADVOCATES:
            return action.advocates.length < PAGE_SIZE;
        default:
            return state;
    }
};

const searchInternalReducer = combineReducers({
    query: queryReducer,
    advocateList: advocateListReducer,
    advocates: advocateReducer,
    limit: limitReducer,
    finished: finishedReducer,
});

const searchReducer = (state, action) => {
    if (action.type === SET_QUERY && state && state.get('query') === action.query) {
        return state;
    } else {
        return searchInternalReducer(state, action);
    }
};

export default searchReducer;
