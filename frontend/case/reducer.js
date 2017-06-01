import {combineReducers} from 'redux-immutable';
import {Map} from 'immutable';
import moment from 'moment';
import {dateFormat} from '../util';
import {CaseDetail, Document} from '../model';
import {SET_ID, SET_DETAIL, OPEN_DISPUTE_FORM, SET_DISPUTED} from './actions';

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

const disputeFormReducer = (state = false, action) => {
    switch (action.type) {
        case OPEN_DISPUTE_FORM:
            return true;
        case SET_ID:
        case SET_DISPUTED:
            return false;
        default:
            return state;
    }
};
const disputedReducer = (state = false, action) => {
    switch (action.type) {
        case SET_DISPUTED:
            return true;
        case SET_ID:
            return false;
        default:
            return state;
    }
};

const loadTimeReducer = (state = null, action) => (action.type === SET_ID ? moment().format(dateFormat) : state);

const reducer = combineReducers({
    id: idReducer,
    detail: detailReducer,
    documents: documentReducer,
    disputeFormOpen: disputeFormReducer,
    disputed: disputedReducer,
    loadTime: loadTimeReducer,
});

export default (state, action) => {
    if (action.type === SET_ID && state && state.get('id') === action.id) {
        return state;
    } else {
        return reducer(state, action);
    }
};
