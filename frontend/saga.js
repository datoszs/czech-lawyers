import {call} from 'redux-saga/effects';

import router from './router';

export default function* () {
    yield call(router.saga, [

    ]);
}
