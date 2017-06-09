import React from 'react';
import PropTypes from 'prop-types';

const StatisticsBarInverse = ({type}) => (
    <div
        className={`statistics-column-inverse ${type}`}
        style={{height: `${Math.random() * 1.5}em`}}
    />
);

StatisticsBarInverse.propTypes = {
    type: PropTypes.oneOf(['positive', 'negative', 'neutral']).isRequired,
};

export default StatisticsBarInverse;
