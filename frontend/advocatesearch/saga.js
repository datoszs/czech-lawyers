import {put, call} from 'redux-saga/effects';
import autocomplete from '../autocomplete';
import {search} from './modules';

export default function* advocateSearch(params, {query}) {
    yield put(autocomplete.initializeValue(query));
    yield call(search.saga, query);
}
