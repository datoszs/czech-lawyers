import {takeEvery, call} from 'redux-saga/effects';
import {feedbackAPI} from '../serverAPI';
import {SEND_EMAIL} from './actions';

const sendEmailSaga = function* sendEmail({values}) {
    try {
        yield call(feedbackAPI, values.toJS());
    } catch (ex) {
        console.error('Unable to send e-mail feedback.');
    }
};

export default function* contact() {
    yield takeEvery(SEND_EMAIL, sendEmailSaga);
}
