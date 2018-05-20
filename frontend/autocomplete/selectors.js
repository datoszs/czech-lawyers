import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getItems = (state) => getModel(state).get('items');
export const getQuery = (state) => getModel(state).get('query');
