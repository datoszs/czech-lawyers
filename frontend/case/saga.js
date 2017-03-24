import {put, call, select} from 'redux-saga/effects';
import {caseAPI} from '../serverAPI';
import {mapDtoToCaseDetail} from '../model';
import {setId, setDetail} from './actions';
import {isDetailLoaded} from './selectors';

export default function* caseRoute({id}) {
    yield put(setId(id));

    if (!(yield select(isDetailLoaded))) {
        const caseDto = yield call(caseAPI.get, id);
        yield put(setDetail(mapDtoToCaseDetail(caseDto)));
    }
}
