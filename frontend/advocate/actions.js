import {NAME} from './constants';

export const SET_ID = `${NAME}/SET_ID`;
export const SET_ADVOCATE = `${NAME}/SET_ADVOCATE`;
export const SET_COURT_FILTER = `${NAME}/SET_COURT_FILTER`;
export const SET_RESULTS = `${NAME}/SET_RESULTS`;
export const SET_GRAPH_FILTER = `${NAME}/SET_GRAPH_FILTER`;
export const SET_CASES = `${NAME}/SET_CASES`;

export const setId = (id) => ({
    type: SET_ID,
    id,
});

export const setAdvocate = (advocate) => ({
    type: SET_ADVOCATE,
    advocate,
});

export const setCourtFilter = (court) => ({
    type: SET_COURT_FILTER,
    court,
});

export const setResults = (results) => ({
    type: SET_RESULTS,
    results,
});

export const setGraphFilter = (year, result = null) => ({
    type: SET_GRAPH_FILTER,
    year,
    result,
});

export const setCases = (cases) => ({
    type: SET_CASES,
    cases,
});
