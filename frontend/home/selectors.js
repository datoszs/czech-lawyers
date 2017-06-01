import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getTop = (state) => getModel(state).get('top');
export const getBottom = (state) => getModel(state).get('bottom');
export const getName = (state, id) => getModel(state).getIn(['names', id]);
