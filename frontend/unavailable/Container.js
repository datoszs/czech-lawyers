import React from 'react';
import {connect} from 'react-redux';
import {withRouter} from 'react-router-dom';
import {isAvailable} from './selectors';
import Screen from './Screen';

const mapStateToProps = (state, {children}) => ({
    children: isAvailable(state) ? children : <Screen />,
});

export default withRouter(connect(mapStateToProps)(({children}) => <div>{children}</div>));
