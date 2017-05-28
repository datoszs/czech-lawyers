import {NAME} from './constants';

const getModel = (state) => state.get(NAME);
export const isSuccess = (state, formName) => getModel(state).hasIn(['success', formName]);
export const getError = (state, formName) => getModel(state).getIn(['error', formName]);
export const isVisible = (state, formName) => isSuccess(state, formName) || !!getError(state, formName);
