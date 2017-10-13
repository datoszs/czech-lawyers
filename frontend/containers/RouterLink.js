import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {wrapEventStop} from '../util';
import router from '../router';

const RouterLinkComponent = ({onClick, children, className}) => <a href="/" className={className} onClick={wrapEventStop(onClick)}>{children}</a>;

RouterLinkComponent.propTypes = {
    onClick: PropTypes.func.isRequired,
    children: PropTypes.node,
    className: PropTypes.string,
};

RouterLinkComponent.defaultProps = {
    className: null,
    children: null,
};

const mapDispatchToProps = (dispatch, {route, params, query, anchor}) => ({
    onClick: () => dispatch(router.transition(route, params, query, anchor)),
});

const RouterLink = connect(undefined, mapDispatchToProps)(RouterLinkComponent);

RouterLink.propTypes = {
    route: PropTypes.string.isRequired,
    params: PropTypes.object, // eslint-disable-line react/forbid-prop-types
    query: PropTypes.object, // eslint-disable-line react/forbid-prop-types
    anchor: PropTypes.string,
    children: PropTypes.node,
    className: PropTypes.string,
};

RouterLink.defaultProps = {
    params: undefined,
    query: undefined,
    anchor: undefined,
    className: undefined,
    children: undefined,
};

export default RouterLink;
