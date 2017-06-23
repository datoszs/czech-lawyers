import {all, call, fork} from 'redux-saga/effects';

import autocomplete from './autocomplete';
import courtStatistics from './courtstatistics';

import router from './router';
import advocateSearch from './advocatesearch';
import advocate from './advocate';
import caseDetail from './case';
import contact from './contact';
import caseSearch from './casesearch';
import link from './link';
import home from './home';
import about from './about';

export default function* () {
    yield all([autocomplete.saga, courtStatistics.saga].map(fork));
    yield call(router.saga, [
        advocateSearch,
        advocate,
        caseDetail,
        contact,
        caseSearch,
        link,
        home,
        about,
    ]);
}
