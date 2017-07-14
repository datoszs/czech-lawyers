import {NAME} from './constants';

const getModel = (state) => state.get(NAME);
export const isAvailable = getModel;
