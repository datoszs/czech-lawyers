import React from 'react';
import PropTypes from 'prop-types';
import styles from './PageSubheader.less';

const PageSubheader = ({children}) => (
    <div className={styles.main}>
        <h2>{children}</h2>
        <hr />
    </div>
);

PageSubheader.propTypes = {
    children: PropTypes.node.isRequired,
};

export default PageSubheader;
