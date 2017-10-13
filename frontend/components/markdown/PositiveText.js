import React from 'react';
import PropTypes from 'prop-types';
import styles from './index.css';

const PositiveText = ({text}) => <span className={styles.positive}>{text}</span>;

PositiveText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default PositiveText;
