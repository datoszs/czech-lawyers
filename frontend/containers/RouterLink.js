import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {wrapLinkMouseEvent} from '../util';
import router from '../router';

const RouterLinkComponent = ({onClick, children, href, className}) => <a href={href} className={className} onClick={wrapLinkMouseEvent(onClick)}>{children}</a>;

RouterLinkComponent.propTypes = {
    onClick: PropTypes.func.isRequired,
    children: PropTypes.node.isRequired,
    href: PropTypes.string.isRequired,
    className: PropTypes.string,
};

RouterLinkComponent.defaultProps = {
    className: null,
};

const mapStateToProps = (state, {route, params, query, anchor}) => ({
    href: router.getHref(state, route, params, query, anchor),
});

const mapDispatchToProps = (dispatch, {route, params, query, anchor}) => ({
    onClick: () => dispatch(router.transition(route, params, query, anchor)),
});

const RouterLink = connect(mapStateToProps, mapDispatchToProps)(RouterLinkComponent);

RouterLink.propTypes = {
    route: PropTypes.string.isRequired,
    params: PropTypes.object, // eslint-disable-line react/forbid-prop-types
    query: PropTypes.object, // eslint-disable-line react/forbid-prop-types
    anchor: PropTypes.string,
    children: PropTypes.node.isRequired,
    className: PropTypes.string,
};

RouterLink.defaultProps = {
    params: undefined,
    query: undefined,
    anchor: undefined,
    className: undefined,
};

export default RouterLink;
