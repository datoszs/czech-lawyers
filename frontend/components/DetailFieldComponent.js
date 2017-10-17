import React from 'react';
import PropTypes from 'prop-types';
import styles from './DetailFieldComponent.less';

const DetailFieldComponent = ({label, children}) => (
    <div className={styles.main}>
        <div className={styles.label}>{label}</div>
        <div>{children}</div>
    </div>
);

DetailFieldComponent.propTypes = {
    label: PropTypes.string,
    children: PropTypes.node,
};

DetailFieldComponent.defaultProps = {
    children: null,
    label: '',
};

export default DetailFieldComponent;
