export const createSetQueryType = (prefix) => `${prefix}/SET_QUERY`;
export const createAddResultsType = (prefix) => `${prefix}/ADD_RESULTS`;
export const createLoadMoreType = (prefix) => `${prefix}/LOAD_MORE`;

export const setQuery = (prefix) => (query = '') => ({
    type: createSetQueryType(prefix),
    query,
});

export const addResults = (prefix) => (results) => ({
    type: createAddResultsType(prefix),
    results,
});

export const loadMore = (prefix) => () => ({
    type: createLoadMoreType(prefix),
});
