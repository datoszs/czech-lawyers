import React from 'react';
import PropTypes from 'prop-types';
import styles from './TwoColumn.less';

const TwoColumn = ({children}) => <div className={styles.main}>{children}</div>;

TwoColumn.propTypes = {
    children: PropTypes.node,
};

TwoColumn.defaultProps = {
    children: null,
};

export default TwoColumn;
