import React from 'react';
import PropTypes from 'prop-types';

export default (Component) => {
    class LifecycleListener extends React.Component {
        componentWillUnmount() {
            this.props.onUnmount();
        }
        render() {
            const props = Object.assign({}, this.props, {onUnmount: undefined});
            return <Component {...props} />;
        }
    }
    LifecycleListener.propTypes = {
        onUnmount: PropTypes.func,
    };
    LifecycleListener.defaultProps = {
        onUnmount: () => {},
    };
    LifecycleListener.displayName = `LifecycleListener(${Component.displayName || Component.name || 'Component'})`;

    return LifecycleListener;
};
