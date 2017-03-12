import {combineReducers} from 'redux-immutable';

import translate from './translate';
import search from './search';
import autocomplete from './autocomplete';

export default combineReducers([
    translate,
    search,
    autocomplete,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {}));
