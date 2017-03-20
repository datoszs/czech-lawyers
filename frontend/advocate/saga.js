import {put, select, fork, call, race, take, takeLatest} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateDetail, mapDtoToAdvocateResults} from '../model';
import {setId, setAdvocate, setResults, SET_COURT_FILTER} from './actions';
import {isAdvocateLoaded, isResultsLoaded, getCourtFilter} from './selectors';

const loadAdvocateSaga = function* loadAdvocate(id) {
    if (!(yield select(isAdvocateLoaded))) {
        const advocate = yield call(advocateAPI.get, id);
        yield put(setAdvocate(mapDtoToAdvocateDetail(advocate)));
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

export default function* advocateDetail({id}) {
    yield put(setId(id));
    yield [
        fork(loadAdvocateSaga, id),
        fork(loadResultsSaga, id),
    ];
}
