import React from 'react';
import PropTypes from 'prop-types';
import styles from './Center.css';

const Center = ({children}) => <div className={styles.center}>{children}</div>;

Center.propTypes = {
    children: PropTypes.node.isRequired,
};

export default Center;
