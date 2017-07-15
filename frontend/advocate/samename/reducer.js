import {combineReducers} from 'redux-immutable';
import {List, Map} from 'immutable';
import {SET_SAME_NAME_ADVOCATES} from './actions';
import {SET_ID} from '../actions';

const idReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_ID:
            return List();
        case SET_SAME_NAME_ADVOCATES:
            return List(action.advocates.map((advocate) => advocate.id_advocate));
        default:
            return state;
    }
};

const nameReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_ID:
            return Map();
        case SET_SAME_NAME_ADVOCATES:
            return Map(action.advocates.map((advocate) => [advocate.id_advocate, advocate.fullname]));
        default:
            return state;
    }
};

const residenceReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_ID:
            return Map();
        case SET_SAME_NAME_ADVOCATES:
            return Map(action.advocates.map((advocate) => [advocate.id_advocate, advocate.residence.city]));
        default:
            return state;
    }
};

export default combineReducers({
    ids: idReducer,
    names: nameReducer,
    residence: residenceReducer,
});
