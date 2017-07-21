import React from 'react';
import {connect} from 'react-redux';
import {If} from '../../util';

const mapStateToProps = (state, {module}) => ({
    hasQuery: !!module.getQuery(state),
    isLoading: module.isLoading(state),
    canLoadMore: module.canLoadMore(state),
    hasResults: module.getCount(state) > 0,
});


const mergeProps = ({hasQuery, isLoading, canLoadMore, hasResults}) => {
    let component = null;
    if (isLoading) {
        component = () => <div>Loading...</div>;
    } else if (canLoadMore) {
        component = () => <div>More to load.</div>;
    } else if (!hasResults && hasQuery) {
        component = () => <div>No results found</div>;
    }
    return {
        test: component !== null,
        Component: component,
    };
};

export default connect(mapStateToProps, undefined, mergeProps)(If);
