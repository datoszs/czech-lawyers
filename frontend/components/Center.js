import React from 'react';
import PropTypes from 'prop-types';
import styles from './Center.less';

const Center = ({children}) => <div className={styles.main}>{children}</div>;

Center.propTypes = {
    children: PropTypes.node.isRequired,
};

export default Center;
