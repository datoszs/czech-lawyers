import {combineReducers} from 'redux-immutable';
import {reducer as formReducer} from 'redux-form/immutable';

import translate from './translate';
import router from './router';
import search from './search';
import autocomplete from './autocomplete';
import advocate from './advocate';
import caseDetail from './case';

export default combineReducers([
    translate,
    router,
    search,
    autocomplete,
    advocate,
    caseDetail,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {
    form: formReducer,
}));
