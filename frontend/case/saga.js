import {put, call, select, takeEvery} from 'redux-saga/effects';
import {caseAPI} from '../serverAPI';
import {mapDtoToCaseDetail} from '../model';
import {setId, setDetail, DISPUTE} from './actions';
import {isDetailLoaded, getLoadTime} from './selectors';

const sendDisputeSaga = function* sendDispute(id, {values}) {
    try {
        const loadTime = yield select(getLoadTime);
        yield call(caseAPI.dispute, id, Object.assign(values.toJS(), {
            datetime: loadTime,
            disputed_tagging: 'both',
        }));
    } catch (ex) {
        console.error('Unable to send dispute');
    }
};

export default function* caseRoute({id}) {
    yield put(setId(id));

    if (!(yield select(isDetailLoaded))) {
        const caseDto = yield call(caseAPI.get, id);
        yield put(setDetail(mapDtoToCaseDetail(caseDto)));
    }

    yield takeEvery(DISPUTE, sendDisputeSaga, id);
}
