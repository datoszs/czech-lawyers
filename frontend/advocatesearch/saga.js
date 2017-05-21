import {call} from 'redux-saga/effects';
import {search} from './modules';

export default function* advocateSearch(params, {query}) {
    yield call(search.saga, query);
}
