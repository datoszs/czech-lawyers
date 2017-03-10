import invariant from 'invariant';
import {Record} from 'immutable';
import {combineReducers} from 'redux-immutable';

export default class TestingStore extends Record({state: null, reducer: null}) {
    constructor(name, reducer) {
        invariant(name, 'Name must be specified');
        invariant(typeof reducer === 'function', 'Reducer must be a function');
        const finalReducer = combineReducers({[name]: reducer});
        super({
            reducer: finalReducer,
            state: finalReducer(undefined, {}),
        });
    }

    apply(...actions) {
        const newState = actions.reduce(this.reducer, this.state);
        return this.set('state', newState);
    }

    select(selector, ...parameters) {
        return selector(this.state, ...parameters);
    }
}
