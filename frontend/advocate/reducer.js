import {combineReducers} from 'redux-immutable';
import {Map, List} from 'immutable';
import {getCurrentYear} from '../util';
import {AdvocateDetail, Statistics, Case} from '../model';
import {SET_ID, SET_ADVOCATE, SET_RESULTS, SET_COURT_FILTER, SET_GRAPH_FILTER, SET_CASES} from './actions';

const idReducer = (state = null, action) => (action.type === SET_ID ? action.id : state);

const advocateReducer = (state = null, action) => {
    switch (action.type) {
        case SET_ADVOCATE:
            return new AdvocateDetail(action.advocate);
        case SET_ID:
            return null;
        default:
            return state;
    }
};

const courtFilterReducer = (state = null, action) => {
    switch (action.type) {
        case SET_COURT_FILTER:
            return action.court;
        case SET_ID:
            return null;
        default:
            return state;
    }
};

const resultsReducer = (state = null, action) => {
    switch (action.type) {
        case SET_RESULTS:
            return Map(Object.entries(action.results).map(([year, statistics]) => [parseInt(year, 10), new Statistics(statistics)]));
        case SET_ID:
        case SET_COURT_FILTER:
            return null;
        default:
            return state;
    }
};

const startYearReducer = (state = getCurrentYear(), action) => {
    switch (action.type) {
        case SET_RESULTS:
            return Math.min(...Object.keys(action.results).map((year) => parseInt(year, 10)));
        case SET_ID:
        case SET_COURT_FILTER:
            return getCurrentYear();
        default:
            return state;
    }
};

const maxCasesReducer = (state = null, action) => {
    switch (action.type) {
        case SET_RESULTS:
            return Math.max(
                state,
                Object.values(action.results)
                    .map((statistics) => (statistics.positive || 0) + (statistics.negative || 0) + (statistics.neutral || 0))
                    .reduce((result, year) => Math.max(result, year)),
            );
        case SET_ID:
            return null;
        default:
            return state;
    }
};

const yearFilterReducer = (state = null, action) => {
    switch (action.type) {
        case SET_ID:
        case SET_COURT_FILTER:
            return null;
        case SET_GRAPH_FILTER:
            return action.year;
        default:
            return state;
    }
};

const resultFilterReducer = (state = null, action) => {
    switch (action.type) {
        case SET_ID:
        case SET_COURT_FILTER:
            return null;
        case SET_GRAPH_FILTER:
            return action.result;
        default:
            return state;
    }
};

const caseListReducer = (state = List(), action) => {
    switch (action.type) {
        case SET_ID:
        case SET_COURT_FILTER:
        case SET_GRAPH_FILTER:
            return List();
        case SET_CASES:
            return List(action.cases.map(({id}) => id));
        default:
            return state;
    }
};

const caseReducer = (state = Map(), action) => {
    switch (action.type) {
        case SET_ID:
        case SET_COURT_FILTER:
        case SET_GRAPH_FILTER:
            return Map();
        case SET_CASES:
            return Map(action.cases.map((caseObj) => [caseObj.id, new Case(caseObj)]));
        default:
            return state;
    }
};

const reducer = combineReducers({
    id: idReducer,
    advocate: advocateReducer,
    courtFilter: courtFilterReducer,
    results: resultsReducer,
    startYear: startYearReducer,
    maxCases: maxCasesReducer,
    yearFilter: yearFilterReducer,
    resultFilter: resultFilterReducer,
    caseList: caseListReducer,
    cases: caseReducer,
});

export default (state, action) => {
    if (action.type === SET_ID && state && state.get('id') === action.id) {
        return state;
    } else {
        return reducer(state, action);
    }
};
