import {combineReducers} from 'redux-immutable';
import {toObject} from '../util';
import {SET_ROUTE} from './actions';
import submodules from './submodules';

const typeReducer = (state = null, action) => (action.type === SET_ROUTE ? action.routeType : state);

const reducerMap = submodules
    .filter((submodule) => !!submodule.reducer)
    .map((submodule) => [submodule.NAME, submodule.reducer])
    .reduce(toObject, {type: typeReducer});

export default combineReducers(reducerMap);
