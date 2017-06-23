import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

export const getStatistics = (state, court) => getModel(state).get(court);
