import React from 'react';
import PropTypes from 'prop-types';

const StatisticsBar = ({number, max, type}) => (
    <div className={`statistics-column ${type}`}>
        <div className="text">{number}</div>
        <div
            className="bar"
            style={{height: `${(number / max) * 150}%`}}
        />
    </div>
);

StatisticsBar.propTypes = {
    number: PropTypes.number.isRequired,
    max: PropTypes.number.isRequired,
    type: PropTypes.oneOf(['positive', 'negative', 'neutral']).isRequired,
};

export default StatisticsBar;
