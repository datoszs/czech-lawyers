import {getModel as getParentModel} from '../selectors';
import {NAME} from './constants';

const getModel = (state) => getParentModel(state).get(NAME);

export const getCaseId = (state) => getModel(state).get('caseId');
export const getResult = (state) => getModel(state).get('result');
export const isLoading = (state) => !getResult(state);
