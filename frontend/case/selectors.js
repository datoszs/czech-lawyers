import {List} from 'immutable';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getDetail = (state) => getModel(state).get('detail');
export const isDetailLoaded = (state) => !!getDetail(state);
export const getDocuments = (state) => getModel(state).getIn(['detail', 'documents'], List());
export const getDocument = (state, id) => getModel(state).getIn(['documents', id]);
