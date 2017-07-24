import {connect} from 'react-redux';
import {If} from '../../util';
import LoadMore from './LoadMore';
import Loading from './Loading';
import NoResults from './NoResults';

const mapStateToProps = (state, {module}) => ({
    hasQuery: !!module.getQuery(state),
    isLoading: module.isLoading(state),
    canLoadMore: module.canLoadMore(state),
    hasResults: module.getCount(state) > 0,
});


const mergeProps = ({hasQuery, isLoading, canLoadMore, hasResults}, dispatchProps, {module}) => {
    let component = null;
    if (isLoading) {
        component = Loading;
    } else if (canLoadMore) {
        component = LoadMore;
    } else if (!hasResults && hasQuery) {
        component = NoResults;
    }
    return {
        test: component !== null,
        Component: component,
        search: module,
    };
};

export default connect(mapStateToProps, undefined, mergeProps)(If);
