import {Record} from 'immutable';
import {TestingStore} from '../util';
import reducer from './reducer';
import {setQuery as createSetQuery} from './actions';
import {isLoading as createIsLoading, canLoadMore as createLoadMore, getIds as createGetIds} from './selectors';

describe('Search module', () => {
    const NAME = 'search';
    const path = [NAME];
    const isLoading = createIsLoading(path);
    const canLoadMore = createLoadMore(path);
    const getIds = createGetIds(path);
    const setQuery = createSetQuery(NAME);
    const Model = Record({id: 0});

    const initial = new TestingStore(NAME, reducer(NAME, Model));
    let store = initial;
    describe('initial state', () => {
        it('given initial state', () => {});
        it('then there are no advocates', () => {
            store.select(getIds).should.be.empty();
        });
        it('and it is not loading', () => {
            store.select(isLoading).should.be.false();
        });
        it('and it cannot load more', () => {
            store.select(canLoadMore).should.be.false();
        });
    });
    describe('query initialization', () => {
        it('when search is initialized', () => {
            store = initial.apply(setQuery('Novák'));
        });
        it('then there are no advocates', () => {
            store.select(getIds).should.be.empty();
        });
        it('and it can load more', () => {
            store.select(canLoadMore).should.be.true();
        });
        it('and it is loading', () => {
            store.select(isLoading).should.be.true();
        });
    });
    describe('empty query initialization', () => {
        it('when search is initialized with empty query', () => {
            store = initial.apply(setQuery());
        });
        it('then there are no advocates', () => {
            store.select(getIds).should.be.empty();
        });
        it('and it is not loading', () => {
            store.select(isLoading).should.be.false();
        });
        it('and it cannot load more', () => {
            store.select(canLoadMore).should.be.false();
        });
    });
    describe('loading advocates', () => {
        it('when advocates are added');
        it('then their IDs are in the list');
        it('and they can be displayed');
        it('and their number agrees');
    });
    describe('loading additional advocates', () => {
        it('when more advocates are added');
        it('then their IDs are appended to the list');
        it('and they can be displayed');
        it('and total number of advocates agrees');
    });
    describe('loading all advocates', () => {
        it('when less then page size advocates are added');
        it('then it cannot load more');
        it('and it is not loading');
    });
    describe('reaching limit', () => {
        it('when advocates are added to the limit');
        it('then it can load more');
        it('and it is not loading');
    });
    describe('incrementing limit', () => {
        it('given that advocates have reached the limit');
        it('when more are requested');
        it('then it is loading');
    });
    describe('resetting query', () => {
        it('given advocates are loaded');
        it('when query is reset');
        it('then they can be displayed');
    });
    describe('resetting query while loading', () => {
        it('when advocates are loading');
        it('and query is reset');
        it('then they are loading');
    });
    describe('resetting query after loading', () => {
        it('when advocates are not loading');
        it('and query is reset');
        it('then they are not loading');
    });
    describe('changing query', () => {
        it('when advocates are loaded');
        it('and query is changed');
        it('then there are no advocates');
    });
    describe('changing query while loading', () => {
        it('when advocates are loading');
        it('and query is changed');
        it('then it is loading');
    });
    describe('changing query after loading', () => {
        it('when advocates are not loading');
        it('and query is changed');
        it('then it is loading');
    });
    describe('changing to empty query while loading', () => {
        it('when advocates are loading');
        it('and query is changed to empty');
        it('then it is not loading');
    });
    describe('changing to empty query after loading', () => {
        it('when advocates are not loading');
        it('and query is changed to empty');
        it('then it is not loading');
    });
});
