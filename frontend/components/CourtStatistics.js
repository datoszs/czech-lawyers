import React from 'react';
import PropTypes from 'prop-types';
import Statistics from './Statistics';

const CourtStatistics = ({positive, negative, neutral, court}) => (
    <div className="court-statistics">
        <div className="court-name">{court}</div>
        <Statistics positive={positive} negative={negative} neutral={neutral} />
    </div>
);

CourtStatistics.propTypes = {
    court: PropTypes.string.isRequired,
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
};

CourtStatistics.defaultProps = {
    positive: 0,
    negative: 0,
    neutral: 0,
};

export default CourtStatistics;
