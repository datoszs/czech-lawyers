import {combineReducers} from 'redux-immutable';

import translate from './translate';
import search from './search';
import autocomplete from './autocomplete';
import advocate from './advocate';

export default combineReducers([
    translate,
    search,
    autocomplete,
    advocate,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {}));
