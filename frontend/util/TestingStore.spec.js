import {Record, Map} from 'immutable';
import TestingStore from './TestingStore';

describe('Testing Store', () => {
    const set = (value) => ({type: 'SET', value});
    const reducer = (state = 42, action) => (action.type === 'SET' ? action.value : state);
    const get = (state) => state.get('NAME');
    const store = new TestingStore('NAME', reducer);

    const advancedSet = (key, value) => ({type: 'SET', key, value});
    const advancedReducer = (state = Map(), action) => (action.type === 'SET' ? state.set(action.key, action.value) : state);
    const advancedGet = (state, key) => state.getIn(['NAME', key]);
    const advancedStore = new TestingStore('NAME', advancedReducer);

    describe('constructor', () => {
        it('creates immutable record', () => {
            store.should.be.an.instanceof(Record);
        });
        it('creates reducer', () => {
            store.reducer.should.be.a('function');
        });
        it('initializes state', () => {
            store.state.should.be.an.instanceof(Map);
            get(store.state).should.equal(42);
        });
    });
    describe('apply', () => {
        const changed = store.apply(set(12));
        it('returns new object', () => {
            changed.should.not.equal(store);
        });
        it('does not change reducer', () => {
            changed.reducer.should.equal(store.reducer);
        });
        it('changes state', () => {
            changed.state.should.not.equal(store.state);
        });
        it('applies action', () => {
            get(changed.state).should.equal(12);
        });
        it('applies multiple actions', () => {
            const newStore = advancedStore.apply(
                advancedSet('A', 12),
                advancedSet('B', 12),
                advancedSet('A', 42),
            );
            advancedGet(newStore.state, 'A').should.equal(42);
            advancedGet(newStore.state, 'B').should.equal(12);
        });
    });
    describe('select', () => {
        it('returns selector value', () => {
            store.select(get).should.equal(42);
        });
        it('accepts arguments', () => {
            const newStore = advancedStore.apply(advancedSet('A', 42));
            newStore.select(advancedGet, 'A').should.equal(42);
        });
    });
});
