import {put, select, call, take} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocate} from '../model';
import {PAGE_SIZE} from './constants';
import {setQuery, addAdvocates, LOAD_MORE} from './actions';
import {isLoading, getAdvocateCount} from './selectors';

const loadAdvocatesSaga = function* loadAdvocates(query) {
    while (yield select(isLoading)) {
        const count = yield select(getAdvocateCount);
        const advocates = yield call(advocateAPI.search, query, count, PAGE_SIZE);
        yield put(addAdvocates(advocates.map(mapDtoToAdvocate)));
    }
};

export default function* advocateSearch(params, {query}) {
    yield put(setQuery(query));
    for (;;) {
        yield call(loadAdvocatesSaga, query);
        yield take(LOAD_MORE);
    }
}
