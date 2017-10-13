import React from 'react';
import PropTypes from 'prop-types';
import styles from './index.css';

const NegativeText = ({text}) => <span className={styles.negative}>{text}</span>;

NegativeText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default NegativeText;
