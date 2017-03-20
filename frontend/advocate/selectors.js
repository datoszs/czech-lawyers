import {NAME} from './constants';
import {Statistics} from '../model';

const getModel = (state) => state.get(NAME);

export const getId = (state) => getModel(state).get('id');

export const getAdvocate = (state) => getModel(state).get('advocate');

export const isAdvocateLoaded = (state) => !!getAdvocate(state);

export const getStartYear = (state) => getModel(state).get('startYear');

const emptyResults = new Statistics({});
export const getResults = (state, year) => getModel(state).getIn(['results', year], emptyResults);

const getSpecificResults = (property) => (state, year) => getModel(state).getIn(['results', year, property], null);

export const getPositive = getSpecificResults('positive');
export const getNegative = getSpecificResults('negative');
export const getNeutral = getSpecificResults('neutral');
