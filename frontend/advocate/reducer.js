import {combineReducers} from 'redux-immutable';
import {Map} from 'immutable';
import {AdvocateDetail, Statistics} from '../model';
import {SET_ID, SET_ADVOCATE, SET_RESULTS} from './actions';

const idReducer = (state = null, action) => (action.type === SET_ID ? action.id : state);

const advocateReducer = (state = null, action) => {
    switch (action.type) {
        case SET_ADVOCATE:
            return new AdvocateDetail(action.advocate);
        case SET_ID:
            return null;
        default:
            return state;
    }
};

const resultsReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_RESULTS:
            return Map(Object.entries(action.results).map(([year, statistics]) => [parseInt(year, 10), new Statistics(statistics)]));
        case SET_ID:
            return Map();
        default:
            return state;
    }
};

const reducer = combineReducers({
    id: idReducer,
    advocate: advocateReducer,
    results: resultsReducer,
});

export default (state, action) => {
    if (action.type === SET_ID && state && state.get('id') === action.id) {
        return state;
    } else {
        return reducer(state, action);
    }
};