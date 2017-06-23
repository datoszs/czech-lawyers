import React from 'react';
import PropTypes from 'prop-types';

const StatisticsBar = ({number, max, type}) => (
    <div
        className={`statistics-bar-${type}`}
        style={{height: `${(number / max) * 1.5}em`}}
    />
);

StatisticsBar.propTypes = {
    number: PropTypes.number,
    max: PropTypes.number.isRequired,
    type: PropTypes.string.isRequired,
};

StatisticsBar.defaultProps = {
    number: 0,
};

export default StatisticsBar;
