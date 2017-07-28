import {loadMore, setQuery} from './actions';
import {getIds, canLoadMore, getResult, isLoading, getQuery, getCount} from './selectors';
import reducer from './reducer';
import saga from './saga';

export default ({reducerPath, actionPrefix, api, Model, transformation}) => ({
    setQuery: setQuery(actionPrefix),
    loadMore: loadMore(actionPrefix),
    getIds: getIds(reducerPath),
    getResult: getResult(reducerPath),
    getQuery: getQuery(reducerPath),
    canLoadMore: canLoadMore(reducerPath),
    isLoading: isLoading(reducerPath),
    reducer: reducer(actionPrefix, Model),
    saga: saga(actionPrefix, reducerPath, api, transformation),
    getCount: getCount(reducerPath),
});
