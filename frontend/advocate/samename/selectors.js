import {getModel as getParentModel} from '../selectors';
import {NAME} from './constants';

const getModel = (state) => getParentModel(state).get(NAME);

export const getIds = (state) => getModel(state).get('ids');
export const getName = (state, id) => getModel(state).getIn(['names', id]);
export const getResidence = (state, id) => getModel(state).getIn(['residence', id]);
