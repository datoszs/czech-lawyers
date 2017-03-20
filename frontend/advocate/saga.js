import {put, select, fork, call} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateDetail, mapDtoToAdvocateResults} from '../model';
import {setId, setAdvocate, setResults} from './actions';
import {isAdvocateLoaded} from './selectors';

const loadAdvocateSaga = function* loadAdvocate(id) {
    if (!(yield select(isAdvocateLoaded))) {
        const [advocate, results] = yield [call(advocateAPI.get, id), call(advocateAPI.getResults, id)];
        yield put(setAdvocate(mapDtoToAdvocateDetail(advocate)));
        yield put(setResults(mapDtoToAdvocateResults(results)));
    }
};

export default function* advocateDetail({id}) {
    yield put(setId(id));
    yield fork(loadAdvocateSaga, id);
}
