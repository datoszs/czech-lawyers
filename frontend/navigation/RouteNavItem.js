import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {NavItem} from 'react-bootstrap';
import {wrapLinkMouseEvent} from '../util';
import router from '../router';

const mapStateToProps = (state, {module}) => ({
    active: router.isActive(state, module),
    href: router.getCurrentHref(state, module),
});

const mapDispatchToProps = (dispatch, {module}) => ({
    onClick: wrapLinkMouseEvent(() => dispatch(router.navigate(module))),
});

const mergeProps = ({active, href}, {onClick}, {children}) => ({
    active,
    onClick,
    children,
    href,
});

const RouteNavItem = connect(mapStateToProps, mapDispatchToProps, mergeProps)(NavItem);

RouteNavItem.propTypes = {
    module: PropTypes.string.isRequired,
};

export default RouteNavItem;
