import React from 'react';
import PropTypes from 'prop-types';

const getHeight = (number, max) => (max !== 0 ? (number / max) * 1.5 : 0);

const StatisticsBar = ({number, max, className}) => (
    <div
        className={className}
        style={{height: `${getHeight(number, max)}em`}}
    />
);

StatisticsBar.propTypes = {
    number: PropTypes.number,
    max: PropTypes.number.isRequired,
    className: PropTypes.string.isRequired,
};

StatisticsBar.defaultProps = {
    number: 0,
};

export default StatisticsBar;
