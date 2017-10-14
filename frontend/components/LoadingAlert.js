import React from 'react';
import PropTypes from 'prop-types';
import {Alert} from 'react-bootstrap';
import Spinner from 'react-spinkit';
import styles from './LoadingAlert.less';

const LoadingAlert = ({children}) => (
    <Alert bsStyle="warning" className={styles.main}>
        <Spinner name="circle" fadeIn="none" className={styles.spinner} />
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
