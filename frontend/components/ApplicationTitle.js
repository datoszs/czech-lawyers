import React from 'react';
import PropTypes from 'prop-types';
import styles from './ApplicationTitle.less';

const ApplicationTitle = ({children}) => <h1 className={styles.main}>{children}</h1>;

ApplicationTitle.propTypes = {
    children: PropTypes.node.isRequired,
};

export default ApplicationTitle;
