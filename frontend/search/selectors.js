export const getIds = (path) =>
    (state) => state.getIn(path).get('ids');
export const getResult = (path) =>
    (state, id) => state.getIn(path).getIn(['results', id]);
export const getCount = (path) =>
    (state) => getIds(path)(state).size;
export const canLoadMore = (path) =>
    (state) => !state.getIn(path).get('finished');
export const isLoading = (path) =>
    (state) => canLoadMore(path)(state) && getCount(path)(state) < state.getIn(path).get('limit');
export const getQuery = (path) => (state) => state.getIn(path).get('query');
