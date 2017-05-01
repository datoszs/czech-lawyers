import {call, fork} from 'redux-saga/effects';

import autocomplete from './autocomplete';

import router from './router';
import advocateSearch from './advocatesearch';
import home from './home';
import advocate from './advocate';
import caseDetail from './case';
import contact from './contact';
import caseSearch from './casesearch';

export default function* () {
    yield [autocomplete.saga].map(fork);
    yield call(router.saga, [
        advocateSearch,
        home,
        advocate,
        caseDetail,
        contact,
        caseSearch,
    ]);
}
