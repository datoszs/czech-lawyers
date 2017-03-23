import {NAME} from './constants';
import {Statistics} from '../model';

const getModel = (state) => state.get(NAME);

export const getAdvocate = (state) => getModel(state).get('advocate');

export const isAdvocateLoaded = (state) => !!getAdvocate(state);

/* RESULTS */
export const getCourtFilter = (state) => getModel(state).get('courtFilter');

export const isResultsLoaded = (state) => !!getModel(state).get('results');

export const getStartYear = (state) => getModel(state).get('startYear');

const emptyResults = new Statistics({});
export const getResults = (state, year) => getModel(state).getIn(['results', year], emptyResults);

export const getYearFilter = (state) => getModel(state).get('yearFilter');
export const getResultFilter = (state) => getModel(state).get('resultFilter');

export const getCases = (state) => getModel(state).get('caseList');
export const getCase = (state, id) => getModel(state).getIn(['cases', id]);
export const areCasesLoaded = (state) => !!getCases(state).size;
