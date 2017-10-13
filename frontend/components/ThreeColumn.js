import React from 'react';
import PropTypes from 'prop-types';
import styles from './ThreeColumn.css';

const ThreeColumn = ({children}) => <div className={styles.main}>{children}</div>;

ThreeColumn.propTypes = {
    children: PropTypes.node,
};

ThreeColumn.defaultProps = {
    children: null,
};

export default ThreeColumn;
