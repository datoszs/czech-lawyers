import {transition} from '../util';
import advocateSearch from '../advocatesearch';
import advocate from '../advocate';

export const setQuery = (query) => transition(advocateSearch.ROUTE, undefined, {query});

export const setAdvocate = (id) => transition(advocate.ROUTE, {id});
