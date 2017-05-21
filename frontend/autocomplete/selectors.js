import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getInputValue = (state) => getModel(state).get('input');

export const getResultIds = (state) => getModel(state).get('resultIds');
export const hasResults = (state) => getResultIds(state).size > 0;
export const getResult = (state, id) => getModel(state).getIn(['results', id]);

export const getSelectedItem = (state) => getModel(state).get('selected');
