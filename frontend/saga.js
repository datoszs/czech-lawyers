import {call} from 'redux-saga/effects';

import router from './router';
import search from './search';

export default function* () {
    yield call(router.saga, [
        search,
    ]);
}
