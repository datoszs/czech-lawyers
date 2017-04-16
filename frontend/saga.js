import {call} from 'redux-saga/effects';

import router from './router';
import search from './search';
import home from './home';
import advocate from './advocate';
import caseDetail from './case';
import contact from './contact';

export default function* () {
    yield call(router.saga, [
        search,
        home,
        advocate,
        caseDetail,
        contact,
    ]);
}
