import {put, select, fork, call, race, take, takeLatest, all} from 'redux-saga/effects';
import {advocateAPI, caseAPI} from '../serverAPI';
import {mapDtoToAdvocateDetail, mapDtoToAdvocateResults, mapDtoToCase} from '../model';
import {setId, setAdvocate, setResults, SET_COURT_FILTER, SET_GRAPH_FILTER, setCases, setStatistics} from './actions';
import {isAdvocateLoaded, isResultsLoaded, getCourtFilter, areCasesLoaded, getYearFilter, getResultFilter} from './selectors';
import samename from './samename';

const loadAdvocateSaga = function* loadAdvocate(id) {
    if (!(yield select(isAdvocateLoaded))) {
        const advocate = yield call(advocateAPI.get, id);
        yield put(setAdvocate(mapDtoToAdvocateDetail(advocate)));
        yield put(setStatistics(advocate.court_statistics));
        yield put(samename.setAdvocates(advocate.advocates_with_same_name));
    }
};

const loadSingleResultSaga = function* loadSingleResult(id) {
    const court = yield select(getCourtFilter);
    const results = yield call(advocateAPI.getResults, id, court);
    yield put(setResults(mapDtoToAdvocateResults(results)));
};

const loadFirstResultSaga = function* loadFirstResult(id) {
    yield race({
        load: call(loadSingleResultSaga, id),
        interrupt: take(SET_COURT_FILTER),
    });
};

const loadResultsSaga = function* loadResults(id) {
    if (!(yield select(isResultsLoaded))) {
        yield fork(loadFirstResultSaga, id);
    }
    yield takeLatest(SET_COURT_FILTER, loadSingleResultSaga, id);
};

const loadFilteredCasesSaga = function* loadFilteredCases(id) {
    const [court, year, result] = yield all([
        select(getCourtFilter),
        select(getYearFilter),
        select(getResultFilter),
    ]);
    try {
        const {cases} = yield call(caseAPI.getByAdvocate, id, court, year, result);
        yield put(setCases(cases.map(mapDtoToCase)));
    } catch (ex) {
        console.error('Unable to download case.');
    }
};

const loadFirstCasesSaga = function* loadFirstCases(id) {
    yield race({
        load: call(loadFilteredCasesSaga, id),
        interrupt: take([SET_COURT_FILTER, SET_GRAPH_FILTER]),
    });
};

const loadCasesSaga = function* loadCases(id) {
    if (!(yield select(areCasesLoaded))) {
        yield fork(loadFirstCasesSaga, id);
    }
    yield takeLatest([SET_COURT_FILTER, SET_GRAPH_FILTER], loadFilteredCasesSaga, id);
};

export default function* advocateDetail({id}) {
    yield put(setId(id));
    yield all([
        fork(loadAdvocateSaga, id),
        fork(loadResultsSaga, id),
        fork(loadCasesSaga, id),
    ]);
}
