import {call} from 'redux-saga/effects';

import router from './router';
import advocateSearch from './advocatesearch';
import home from './home';
import advocate from './advocate';
import caseDetail from './case';
import contact from './contact';

export default function* () {
    yield call(router.saga, [
        advocateSearch,
        home,
        advocate,
        caseDetail,
        contact,
    ]);
}
