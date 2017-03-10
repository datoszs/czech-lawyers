import {NAME} from './constants';

const getModel = (state) => state.get(NAME);

const getSearchModel = getModel;

export const getAdvocateIds = (state) => getSearchModel(state).get('advocateList');

export const getAdvocate = (state, id) => getSearchModel(state).getIn(['advocates', id]);

export const getAdvocateCount = (state) => getAdvocateIds(state).size;

export const canLoadMore = (state) => !getSearchModel(state).get('finished');

export const isLoading = (state) => canLoadMore(state) && getAdvocateCount(state) < getSearchModel(state).get('limit');
