import {combineReducers} from 'redux-immutable';

import translate from './translate';

export default combineReducers([
    translate,
].reduce((result, module) => Object.assign({[module.NAME]: module.reducer}, result), {}));
