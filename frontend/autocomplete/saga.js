import {call, put, takeEvery} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateAutocomplete} from '../model';
import {SET_INPUT_VALUE, setAutocompleteResults} from './actions';

const loadOptionsSaga = function* loadOptions({value}) {
    if (value) {
        const result = yield call(advocateAPI.autocomplete, value);
        yield put(setAutocompleteResults(result.map(mapDtoToAdvocateAutocomplete)));
    } else {
        yield put(setAutocompleteResults([]));
    }
};

export default function* autocomplete() {
    yield takeEvery(SET_INPUT_VALUE, loadOptionsSaga);
}
