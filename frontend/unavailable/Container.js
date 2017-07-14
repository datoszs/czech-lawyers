import React from 'react';
import {connect} from 'react-redux';
import {isAvailable} from './selectors';
import Screen from './Screen';

const mapStateToProps = (state, {children}) => ({
    children: isAvailable(state) ? children : <Screen />,
});

export default connect(mapStateToProps)(({children}) => <div>{children}</div>);
