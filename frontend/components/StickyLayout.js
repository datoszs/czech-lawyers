import React from 'react';
import PropTypes from 'prop-types';

import styles from './StickyLayout.less';

const StickyLayout = ({sidebar, children}) => (
    <div className={styles.container}>
        <div className={styles.sidebar}>
            <div className={styles.sticky}>{sidebar}</div>
        </div>
        <div className={styles.main}>{children}</div>
    </div>
);

StickyLayout.propTypes = {
    children: PropTypes.node.isRequired,
    sidebar: PropTypes.node,
};

StickyLayout.defaultProps = {
    sidebar: null,
};


export default StickyLayout;
