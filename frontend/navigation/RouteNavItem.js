import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {NavItem} from 'react-bootstrap';
import router from '../router';

const mapStateToProps = (state, {module}) => ({
    active: router.isActive(state, module),
});

const mapDispatchToProps = (dispatch, {module}) => ({
    onClick: () => dispatch(router.navigate(module)),
});

const mergeProps = ({active}, {onClick}, {children}) => ({
    active,
    onClick,
    children,
});

const RouteNavItem = connect(mapStateToProps, mapDispatchToProps, mergeProps)(NavItem);

RouteNavItem.propTypes = {
    module: PropTypes.string.isRequired,
};

export default RouteNavItem;
