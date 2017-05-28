import {call, put} from 'redux-saga/effects';
import {RequestError} from '../serverAPI';
import {setError, setSuccess} from './actions';

export default function* sendForm(formName, api, values) {
    try {
        yield call(api, values);
        yield put(setSuccess(formName));
    } catch (ex) {
        if (ex instanceof RequestError && ex.response.error) {
            yield put(setError(formName, ex.response.error));
        } else {
            yield put(setError(formName, 'unknown'));
        }
        throw ex;
    }
}
