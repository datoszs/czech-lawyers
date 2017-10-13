import React from 'react';
import PropTypes from 'prop-types';
import styles from './NegativeText.css';

const NegativeText = ({text}) => <span className={styles.main}>{text}</span>;

NegativeText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default NegativeText;
