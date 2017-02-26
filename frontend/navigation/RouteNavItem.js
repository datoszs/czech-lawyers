import React, {PropTypes} from 'react';
import {NavItem} from 'react-bootstrap';
import {routerShape} from 'react-router';

const RouteNavItem = ({route, children}, {router}) => (
    <NavItem
        active={router.isActive(route)}
        onClick={() => router.push(route)}
    >
        {children}
    </NavItem>
);

RouteNavItem.propTypes = {
    route: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
};

RouteNavItem.contextTypes = {
    router: routerShape.isRequired,
};

export default RouteNavItem;
