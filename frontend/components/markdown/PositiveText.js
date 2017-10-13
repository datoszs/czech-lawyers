import React from 'react';
import PropTypes from 'prop-types';
import styles from './PositiveText.css';

const PositiveText = ({text}) => <span className={styles.main}>{text}</span>;

PositiveText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default PositiveText;
