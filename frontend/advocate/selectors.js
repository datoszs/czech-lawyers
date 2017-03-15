import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getId = (state) => getModel(state).get('id');

export const getAdvocate = (state) => getModel(state).get('advocate');

export const isAdvocateLoaded = (state) => !!getAdvocate(state);
