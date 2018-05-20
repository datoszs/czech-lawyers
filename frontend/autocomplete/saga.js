import {call, put, takeLatest} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {mapDtoToAdvocateAutocomplete} from '../model';
import {SET_QUERY, setItems} from './actions';

const loadItemsSaga = function* loadItems({query}) {
    if (query !== '') {
        const items = yield call(advocateAPI.autocomplete, query);
        yield put(setItems(items.slice(0, 10).map(mapDtoToAdvocateAutocomplete)));
    }
};

export default function* autocomplete() {
    yield takeLatest(SET_QUERY, loadItemsSaga);
}
