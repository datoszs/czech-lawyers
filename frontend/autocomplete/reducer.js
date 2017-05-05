import {combineReducers} from 'redux-immutable';
import {List, Map} from 'immutable';
import {AdvocateAutocomplete} from '../model';
import {SET_INPUT_VALUE, INITIALIZE_VALUE, SET_AUTOCOMPLETE_RESULTS,
    SUBMIT, MOVE_SELECTION, SET_SELECTION, setSelection} from './actions';

const inputReducer = (state = '', action) =>
    (action.type === SET_INPUT_VALUE || action.type === INITIALIZE_VALUE ? action.value : state);

const resultIdsReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_AUTOCOMPLETE_RESULTS:
            return List(action.results.map(({id}) => id));
        case SUBMIT:
            return List();
        default:
            return state;
    }
};

const resultReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_AUTOCOMPLETE_RESULTS:
            return Map(action.results.map((result) => [result.id, new AdvocateAutocomplete(result)]));
        case SUBMIT:
            return Map();
        default:
            return state;
    }
};

const selectedItemReducer = (state = null, action) => {
    switch (action.type) {
        case SET_SELECTION:
            return action.id;
        case SET_AUTOCOMPLETE_RESULTS:
            return action.results.some(({id}) => (id === state)) ? state : null;
        case INITIALIZE_VALUE:
            return null;
        default:
            return state;
    }
};

const reducer = combineReducers({
    input: inputReducer,
    resultIds: resultIdsReducer,
    results: resultReducer,
    selected: selectedItemReducer,
});

export default (state, action) => {
    if (action.type === MOVE_SELECTION) {
        const ids = state.get('resultIds');
        const index = ids.indexOf(state.get('selected'));
        const resultIndex = (index !== -1 || action.increment > 0) ? index + action.increment : action.increment;
        const result = ids.get(resultIndex % ids.size);
        return reducer(state, setSelection(result));
    } else {
        return reducer(state, action);
    }
};
