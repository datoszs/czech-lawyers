import React from 'react';
import PropTypes from 'prop-types';
import styles from './PanelBody.less';

const PanelBody = ({children}) => (
    <div className={styles.main}>
        {children}
    </div>
);

PanelBody.propTypes = {
    children: PropTypes.node.isRequired,
};

export default PanelBody;
