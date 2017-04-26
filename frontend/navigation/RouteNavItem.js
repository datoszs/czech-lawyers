import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {NavItem} from 'react-bootstrap';
import {routerShape} from 'react-router';
import routerModule from '../router';

const RouteNavItemComponent = ({route, path, children}, {router}) => (
    <NavItem
        active={router.isActive(route)}
        onClick={() => router.push(`/${path}`)}
    >
        {children}
    </NavItem>
);

RouteNavItemComponent.propTypes = {
    route: PropTypes.string.isRequired,
    path: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
};

RouteNavItemComponent.contextTypes = {
    router: routerShape.isRequired,
};

const mapStateToProps = (state, {route, module}) => ({
    path: routerModule.getCurrentPath(state, module, route),
});

const RouteNavItem = connect(mapStateToProps)(RouteNavItemComponent);

RouteNavItem.propTypes = {
    route: PropTypes.string.isRequired,
    module: PropTypes.string.isRequired,
};

export default RouteNavItem;
