import {List} from 'immutable';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getTop = (state, court) => getModel(state).getIn(['top', court], List());
export const getBottom = (state, court) => getModel(state).getIn(['bottom', court], List());
export const getName = (state, id) => getModel(state).getIn(['names', id]);
