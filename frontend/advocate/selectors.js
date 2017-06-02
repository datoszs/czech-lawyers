import {NAME} from './constants';
import {Statistics} from '../model';

const getModel = (state) => state.get(NAME);

/* DETAIL */
export const getAdvocate = (state) => getModel(state).get('advocate');
export const isAdvocateLoaded = (state) => !!getAdvocate(state);

/* RESULTS */
export const getCourtFilter = (state) => getModel(state).get('courtFilter');
export const isResultsLoaded = (state) => !!getModel(state).get('results');
export const getStartYear = (state) => getModel(state).get('startYear');
export const getMaxCases = (state) => getModel(state).get('maxCases');
const emptyResults = new Statistics({});
export const getResults = (state, year) => getModel(state).getIn(['results', year], emptyResults);

/* FILTER */
export const getYearFilter = (state) => getModel(state).get('yearFilter');
export const getResultFilter = (state) => getModel(state).get('resultFilter');

/* CASES */
export const getCases = (state) => getModel(state).get('caseList');
export const getCase = (state, id) => getModel(state).getIn(['cases', id]);
export const areCasesLoaded = (state) => !!getCases(state).size;

/* STATISTICS */
export const getStatistics = (state, court = null) => getModel(state).getIn(['statistics', court]);
