import {combineReducers} from 'redux-immutable';
import {fromJS, Map} from 'immutable';
import {ROUTE_ENTERED, SET_ROUTE_MAP} from './actions';

const routeInfoReducer = (property) => (state = Map(), action) => {
    if (action.type === ROUTE_ENTERED) {
        return state.set(action.name, fromJS(action[property]));
    } else {
        return state;
    }
};

const routeReducer = (state = null, action) => (action.type === ROUTE_ENTERED ? action.name : state);

const routeMapReducer = (state = Map(), action) => (action.type === SET_ROUTE_MAP ? fromJS(action.routeMap) : state);

export default combineReducers({
    params: routeInfoReducer('params'),
    query: routeInfoReducer('query'),
    route: routeReducer,
    routes: routeMapReducer,
});
