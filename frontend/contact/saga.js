import {takeEvery, call, put} from 'redux-saga/effects';
import {reset} from 'redux-form';
import {feedbackAPI} from '../serverAPI';
import formstatus from '../formstatus';
import {SEND_EMAIL} from './actions';
import {CONTACT_FORM} from './constants';

const sendEmailSaga = function* sendEmail({values}) {
    try {
        yield call(formstatus.saga, CONTACT_FORM, feedbackAPI, values.toJS());
        yield put(reset(CONTACT_FORM));
    } catch (ex) {
        // expected
    }
};

export default function* contact() {
    yield takeEvery(SEND_EMAIL, sendEmailSaga);
}
