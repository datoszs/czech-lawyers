import {combineReducers} from 'redux-immutable';
import {Map, Set} from 'immutable';
import {CLEAR, ERROR, SUCCESS, START_SUBMIT} from './actions';

const successReducer = (state = Set(), action) => {
    switch (action.type) {
        case CLEAR:
        case ERROR:
            return state.remove(action.formName);
        case SUCCESS:
            return state.add(action.formName);
        default:
            return state;
    }
};

const errorReducer = (state = Map(), action) => {
    switch (action.type) {
        case CLEAR:
        case SUCCESS:
            return state.remove(action.formName);
        case ERROR:
            return state.set(action.formName, action.error);
        default:
            return state;
    }
};

const submittingReducer = (state = Set(), action) => {
    switch (action.type) {
        case SUCCESS:
        case ERROR:
            return state.remove(action.formName);
        case START_SUBMIT:
            return state.add(action.formName);
        default:
            return state;
    }
};

export default combineReducers({
    success: successReducer,
    error: errorReducer,
    submitting: submittingReducer,
});
