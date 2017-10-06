import {put, call, select, takeEvery} from 'redux-saga/effects';
import ga from 'react-ga';
import {caseAPI} from '../serverAPI';
import {mapDtoToCaseDetail} from '../model';
import formstatus from '../formstatus';
import {FORM} from './constants';
import {setId, setDetail, DISPUTE, setDisputed} from './actions';
import {isDetailLoaded, getLoadTime} from './selectors';

const sendDisputeSaga = function* sendDispute(id, {values}) {
    try {
        const loadTime = yield select(getLoadTime);
        yield call(formstatus.saga, FORM, caseAPI.dispute(id), Object.assign(values.toJS(), {
            datetime: loadTime,
        }));
        yield put(setDisputed());
    } catch (ex) {
        // expected
    }
};

export default function* caseRoute({id}) {
    yield put(setId(id));

    if (!(yield select(isDetailLoaded))) {
        const caseDto = yield call(caseAPI.get, id);
        yield put(setDetail(mapDtoToCaseDetail(caseDto)));

        ga.event({
            category: 'case',
            action: 'inspect',
            label: caseDto.registry_mark,
        });
    }

    yield takeEvery(DISPUTE, sendDisputeSaga, id);
}
