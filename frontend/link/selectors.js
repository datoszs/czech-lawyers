import {NAME} from './constants';

export const getModel = (state) => state.get(NAME);

export const getType = (state) => getModel(state).get('type');
