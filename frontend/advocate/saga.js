import {put, select, fork, call} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateDetail} from '../model';
import {setId, setAdvocate} from './actions';
import {isAdvocateLoaded} from './selectors';

const loadAdvocateSaga = function* loadAdvocate(id) {
    if (!(yield select(isAdvocateLoaded))) {
        const advocate = yield call(advocateAPI.get, id);
        yield put(setAdvocate(mapDtoToAdvocateDetail(advocate)));
    }
};

export default function* advocateDetail({id}) {
    yield put(setId(id));
    yield fork(loadAdvocateSaga, id);
}
