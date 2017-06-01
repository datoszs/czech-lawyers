import {call, fork} from 'redux-saga/effects';

import autocomplete from './autocomplete';

import router from './router';
import advocateSearch from './advocatesearch';
import advocate from './advocate';
import caseDetail from './case';
import contact from './contact';
import caseSearch from './casesearch';
import link from './link';
import home from './home';

export default function* () {
    yield [autocomplete.saga].map(fork);
    yield call(router.saga, [
        advocateSearch,
        advocate,
        caseDetail,
        contact,
        caseSearch,
        link,
        home,
    ]);
}
