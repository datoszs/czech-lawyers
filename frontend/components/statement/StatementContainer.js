import React from 'react';
import PropTypes from 'prop-types';
import styles from './StatementContainer.css';

const StatementContainer = ({children}) => (
    <div className={styles.main}>
        {children}
    </div>
);

StatementContainer.propTypes = {
    children: PropTypes.node.isRequired,
};

export default StatementContainer;
