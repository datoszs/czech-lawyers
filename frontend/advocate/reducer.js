import {combineReducers} from 'redux-immutable';
import {AdvocateDetail} from '../model';
import {SET_ID, SET_ADVOCATE} from './actions';

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

const reducer = combineReducers({
    id: idReducer,
    advocate: advocateReducer,
});

export default (state, action) => {
    if (action.type === SET_ID && state && state.get('id') === action.id) {
        return state;
    } else {
        return reducer(state, action);
    }
};
