import {combineReducers} from 'redux-immutable';
import {SET_CASE, SET_RESULT} from './actions';

const caseReducer = (state = null, action) => (action.type === SET_CASE ? action.caseId : state);

const resultReducer = (state = null, action) => (action.type === SET_RESULT ? action.result : state);

export default combineReducers({
    caseId: caseReducer,
    result: resultReducer,
});
