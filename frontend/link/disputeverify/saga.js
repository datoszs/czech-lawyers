import {put, call} from 'redux-saga/effects';
import {RequestError, caseAPI} from '../../serverAPI';
import {result} from './constants';
import {setResult, setCase} from './actions';

export default function* ({email, code, id_case: caseId}) {
    yield put(setResult(null));
    if (caseId) {
        yield put(setCase(caseId));
    }

    if (email && code) {
        try {
            yield call(caseAPI.disputeVerify, email, code);
            yield put(setResult(result.SUCCESS));
        } catch (ex) {
            if (ex instanceof RequestError && ex.response.error) {
                yield put(setResult(ex.response.error));
            } else {
                yield put(setResult(result.FAIL));
            }
        }
    } else {
        yield put(setResult(result.INVALID_INPUT));
    }
}
