import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert, Button} from 'react-bootstrap';
import {Msg} from '..';

const LoadMoreComponent = ({count, loadMore}) => (
    <Alert bsStyle="success">
        <Msg msg="search.load.more.text" params={{count}} />
        &nbsp;
        <Button onClick={loadMore}><Msg msg="search.load.more" /></Button>
    </Alert>
);

LoadMoreComponent.propTypes = {
    count: PropTypes.number.isRequired,
    loadMore: PropTypes.func.isRequired,
};

const mapStateToProps = (state, {search}) => ({
    count: search.getCount(state),
});

const mapDispatchToProps = (dispatch, {search}) => ({
    loadMore: () => dispatch(search.loadMore()),
});

export default connect(mapStateToProps, mapDispatchToProps)(LoadMoreComponent);
