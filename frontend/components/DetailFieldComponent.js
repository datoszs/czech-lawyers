import React from 'react';
import PropTypes from 'prop-types';
import styles from './DetailFieldComponent.css'

const DetailFieldComponent = ({label, children}) => (
    <div className={styles.detailField}>
        <div className={styles.detailFieldLabel}>{label}</div>
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
