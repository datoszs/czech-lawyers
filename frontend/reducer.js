import {combineReducers} from 'redux-immutable';
import {reducer as formReducer} from 'redux-form/immutable';

import translate from './translate';
import search from './search';
import autocomplete from './autocomplete';
import advocate from './advocate';
import caseDetail from './case';

export default combineReducers([
    translate,
    search,
    autocomplete,
    advocate,
    caseDetail,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {
    form: formReducer,
}));
