import {put} from 'redux-saga/effects';
import {setQuery} from './actions';

export default function* advocateSearchSaga(params, {query}) {
    // FIXME what if query is not specified?
    yield put(setQuery(query || ''));
}
