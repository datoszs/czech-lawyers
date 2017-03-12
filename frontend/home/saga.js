import {put} from 'redux-saga/effects';
import autocomplete from '../autocomplete';

export default function* home() {
    yield put(autocomplete.initializeValue());
}
