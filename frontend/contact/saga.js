import {takeEvery, call, put} from 'redux-saga/effects';
import {reset} from 'redux-form';
import {feedbackAPI} from '../serverAPI';
import {SEND_EMAIL} from './actions';
import {CONTACT_FORM} from './constants';

const sendEmailSaga = function* sendEmail({values}) {
    try {
        yield call(feedbackAPI, values.toJS());
        yield put(reset(CONTACT_FORM));
    } catch (ex) {
        console.error('Unable to send e-mail feedback.');
    }
};

export default function* contact() {
    yield takeEvery(SEND_EMAIL, sendEmailSaga);
}
