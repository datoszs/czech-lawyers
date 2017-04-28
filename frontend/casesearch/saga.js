import {call, put} from 'redux-saga/effects';
import {initialize} from 'redux-form/immutable';
import {search} from './modules';
import {SEARCH_FORM} from './constants';

export default function* caseSearch(params, {query}) {
    yield put(initialize(SEARCH_FORM, {query}));
    yield call(search.saga, query);
}
