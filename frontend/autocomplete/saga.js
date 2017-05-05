import {call, put, select, takeEvery, takeLatest} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateAutocomplete} from '../model';
import {SET_INPUT_VALUE, SUBMIT, SHOW_DROPDOWN, setAutocompleteResults} from './actions';
import {getInputValue, getSelectedItem} from './selectors';
import {setQuery, setAdvocate} from './transition';

const loadOptionsSaga = function* loadOptions() {
    const value = yield select(getInputValue);
    if (value) {
        const result = yield call(advocateAPI.autocomplete, value);
        yield put(setAutocompleteResults(result.map(mapDtoToAdvocateAutocomplete)));
    } else {
        yield put(setAutocompleteResults([]));
    }
};

const submitSaga = function* submit() {
    const selectedItem = yield select(getSelectedItem);
    if (selectedItem) {
        yield call(setAdvocate, selectedItem);
    } else {
        const query = yield select(getInputValue);
        yield call(setQuery, query);
    }
};

export default function* autocomplete() {
    yield [
        takeLatest([SET_INPUT_VALUE, SHOW_DROPDOWN], loadOptionsSaga),
        takeEvery(SUBMIT, submitSaga),
    ];
}
