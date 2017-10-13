import React from 'react';
import PropTypes from 'prop-types';
import styles from './CurrentSearch.css';

const CurrentSearch = ({query, legend}) => (
    <div className={styles.main}><span className={styles.legend}>{legend}</span> {query}</div>
);

CurrentSearch.propTypes = {
    query: PropTypes.string.isRequired,
    legend: PropTypes.string.isRequired,
};

export default CurrentSearch;
