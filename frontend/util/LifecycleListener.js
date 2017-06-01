import React from 'react';
import PropTypes from 'prop-types';

export default (Component) => {
    class LifecycleListener extends React.Component {
        componentWillUnmount() {
            this.props.onUnmount();
        }
        render() {
            const {onUnmount, ...rest} = this.props; // filter out lifecycle listeners
            return <Component {...rest} />;
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
