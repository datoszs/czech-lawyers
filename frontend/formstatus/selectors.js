import {NAME} from './constants';

const getModel = (state) => state.get(NAME);
export const isSuccess = (state, formName) => getModel(state).hasIn(['success', formName]);
export const getError = (state, formName) => getModel(state).getIn(['error', formName]);
export const isSubmitting = (state, formName) => getModel(state).hasIn(['submitting', formName]);
