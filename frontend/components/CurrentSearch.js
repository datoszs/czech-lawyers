import React from 'react';
import PropTypes from 'prop-types';

const CurrentSearch = ({query, legend}) => (
    <div className="current-search"><span className="legend">{legend}</span> {query}</div>
);

CurrentSearch.propTypes = {
    query: PropTypes.string.isRequired,
    legend: PropTypes.string.isRequired,
};

export default CurrentSearch;
