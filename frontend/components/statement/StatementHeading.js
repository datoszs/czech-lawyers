import React from 'react';
import PropTypes from 'prop-types';
import {Well} from 'react-bootstrap';
import styles from './StatementHeading.less';

const StatementHeading = ({children}) => (
    <Well className={styles.main}>
        {children}
    </Well>
);

StatementHeading.propTypes = {
    children: PropTypes.node.isRequired,
};

export default StatementHeading;
