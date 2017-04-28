import {combineReducers} from 'redux-immutable';
import {List, Map} from 'immutable';
import {LIMIT, LIMIT_INCREMENT, PAGE_SIZE} from './constants';
import {createSetQueryType, createAddResultsType, createLoadMoreType} from './actions';

export default (prefix, Model) => {
    const SET_QUERY = createSetQueryType(prefix);
    const ADD_RESULTS = createAddResultsType(prefix);
    const LOAD_MORE = createLoadMoreType(prefix);

    const queryReducer = (state = '', action) => (action.type === SET_QUERY ? action.query : state);

    const idReducer = (state = List(), action) => {
        switch (action.type) {
            case SET_QUERY:
                return List();
            case ADD_RESULTS:
                return state.concat(action.results.map(({id}) => id));
            default:
                return state;
        }
    };

    const resultReducer = (state = Map(), action) => {
        switch (action.type) {
            case SET_QUERY:
                return Map();
            case ADD_RESULTS:
                return state.merge(Map(action.results.map((result) => [result.id, new Model(result)])));
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
            case ADD_RESULTS:
                return action.results.length < PAGE_SIZE;
            default:
                return state;
        }
    };

    const reducer = combineReducers({
        query: queryReducer,
        ids: idReducer,
        results: resultReducer,
        limit: limitReducer,
        finished: finishedReducer,
    });

    return (state, action) => {
        if (action.type === SET_QUERY && state && state.get('query') === action.query) {
            console.log('Query not changed');
            return state;
        } else {
            return reducer(state, action);
        }
    };
};
