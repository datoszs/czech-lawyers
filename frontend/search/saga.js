import {put, call, select, take} from 'redux-saga/effects';
import {PAGE_SIZE} from './constants';
import {setQuery, addResults, createLoadMoreType} from './actions';
import {isLoading, getCount} from './selectors';

export default (actionPrefix, reducerPath, api, transformation) => {
    const loadSaga = function* load(query) {
        while (yield select(isLoading(reducerPath))) {
            const count = yield select(getCount(reducerPath));
            const results = yield call(api, query, count, PAGE_SIZE);
            yield put(addResults(actionPrefix)(results.map(transformation)));
        }
    };

    return function* search(query) {
        yield put(setQuery(actionPrefix)(query));
        for (;;) {
            yield call(loadSaga, query);
            yield take(createLoadMoreType(actionPrefix));
        }
    };
};
