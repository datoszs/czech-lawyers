import {call, put, select, takeEvery, takeLatest} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateAutocomplete} from '../model';
import {transition} from '../util';
import advocateSearch from '../advocatesearch';
import {SET_INPUT_VALUE, SUBMIT, SHOW_DROPDOWN, setAutocompleteResults} from './actions';
import {getInputValue} from './selectors';

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
    const query = yield select(getInputValue);
    yield call(transition, advocateSearch.ROUTE, undefined, {query});
};

export default function* autocomplete() {
    yield [
        takeLatest([SET_INPUT_VALUE, SHOW_DROPDOWN], loadOptionsSaga),
        takeEvery(SUBMIT, submitSaga),
    ];
}
