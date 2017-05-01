import {combineReducers} from 'redux-immutable';
import {List, Map} from 'immutable';
import {AdvocateAutocomplete} from '../model';
import {SET_INPUT_VALUE, INITIALIZE_VALUE, SET_AUTOCOMPLETE_RESULTS} from './actions';

const inputReducer = (state = '', action) =>
    (action.type === SET_INPUT_VALUE || action.type === INITIALIZE_VALUE ? action.value : state);

const resultIdsReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_AUTOCOMPLETE_RESULTS:
            return List(action.results.map(({id}) => id));
        default:
            return state;
    }
};

const resultReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_AUTOCOMPLETE_RESULTS:
            return Map(action.results.map((result) => [result.id, new AdvocateAutocomplete(result)]));
        default:
            return state;
    }
};

export default combineReducers({
    input: inputReducer,
    resultIds: resultIdsReducer,
    results: resultReducer,
});
