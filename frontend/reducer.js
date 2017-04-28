import {combineReducers} from 'redux-immutable';
import {reducer as formReducer} from 'redux-form/immutable';

import translate from './translate';
import router from './router';
import advocateSearch from './advocatesearch';
import autocomplete from './autocomplete';
import advocate from './advocate';
import caseDetail from './case';
import caseSearch from './casesearch';

export default combineReducers([
    translate,
    router,
    advocateSearch,
    autocomplete,
    advocate,
    caseDetail,
    caseSearch,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {
    form: formReducer,
}));
