import React from 'react';
import PropTypes from 'prop-types';
import {Alert} from 'react-bootstrap';
import Spinner from 'react-spinkit';

const LoadingAlert = ({children}) => (
    <Alert bsStyle="warning" className="loading-alert">
        <Spinner name="circle" fadeIn="none" className="loading-spinner" />
        {children}
    </Alert>
);

LoadingAlert.propTypes = {
    children: PropTypes.node,
};

LoadingAlert.defaultProps = {
    children: null,
};

export default LoadingAlert;
