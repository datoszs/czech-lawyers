import {all, call, put, select, takeEvery, takeLatest} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateAutocomplete} from '../model';
import {SET_INPUT_VALUE, SUBMIT, SHOW_DROPDOWN, MOVE_SELECTION,
    moveSelectionInternal, setAutocompleteResults, setQuery, setAdvocate} from './actions';
import {getInputValue, getSelectedItem, hasResults} from './selectors';

const loadOptionsSaga = function* loadOptions() {
    const value = yield select(getInputValue);
    if (value) {
        const result = yield call(advocateAPI.autocomplete, value);
        yield put(setAutocompleteResults(result.slice(0, 10).map(mapDtoToAdvocateAutocomplete)));
    } else {
        yield put(setAutocompleteResults([]));
    }
};

const loadOptionsControllerSaga = function* loadOptionsController(action) {
    switch (action.type) {
        case SET_INPUT_VALUE:
        case SHOW_DROPDOWN:
            yield call(loadOptionsSaga);
            break;
        case MOVE_SELECTION:
            if (!(yield select(hasResults))) {
                yield call(loadOptionsSaga);
            }
            yield put(moveSelectionInternal(action.increment));
            break;
        default:
            throw new Error(`Unrecognized action type: ${action.type}`);
    }
};

const submitSaga = function* submit() {
    const selectedItem = yield select(getSelectedItem);
    if (selectedItem) {
        yield put(setAdvocate(selectedItem));
    } else {
        const query = yield select(getInputValue);
        yield put(setQuery(query));
    }
};

export default function* autocomplete() {
    yield all([
        takeLatest([SET_INPUT_VALUE, SHOW_DROPDOWN, MOVE_SELECTION], loadOptionsControllerSaga),
        takeEvery(SUBMIT, submitSaga),
    ]);
}
