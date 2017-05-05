import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getInputValue = (state) => getModel(state).get('input');

export const getResultIds = (state) => getModel(state).get('resultIds');

export const getResult = (state, id) => getModel(state).getIn(['results', id]);

export const getSelectedItem = (state) => getModel(state).get('selected');
