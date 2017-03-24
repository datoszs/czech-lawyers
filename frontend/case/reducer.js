import {combineReducers} from 'redux-immutable';
import {Map} from 'immutable';
import {CaseDetail, Document} from '../model';
import {SET_ID, SET_DETAIL} from './actions';

const idReducer = (state = null, action) => (action.type === SET_ID ? action.id : state);

const detailReducer = (state = null, action) => {
    switch (action.type) {
        case SET_ID:
            return null;
        case SET_DETAIL:
            return new CaseDetail(action.detail);
        default:
            return state;
    }
};

const documentReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_ID:
            return Map();
        case SET_DETAIL:
            return Map(action.detail.documents.map((document) => [document.id, new Document(document)]));
        default:
            return state;
    }
};

const reducer = combineReducers({
    id: idReducer,
    detail: detailReducer,
    documents: documentReducer,
});

export default (state, action) => {
    if (action.type === SET_ID && state && state.get('id') === action.id) {
        return state;
    } else {
        return reducer(state, action);
    }
};
