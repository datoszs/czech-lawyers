import {combineReducers} from 'redux-immutable';

import translate from './translate';
import search from './search';

export default combineReducers([
    translate,
    search,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {}));
