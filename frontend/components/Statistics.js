import React, {PropTypes} from 'react';
import StatisticsBar from './StatisticsBar';

const Statistics = ({positive, negative, neutral}) => {
    const max = Math.max(positive, negative, neutral);
    return (
        <h2 className="statistics">
            <StatisticsBar number={positive} max={max} type="positive" />
            <StatisticsBar number={negative} max={max} type="negative" />
            <StatisticsBar number={neutral} max={max} type="neutral" />
        </h2>
    );
};

Statistics.propTypes = {
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
};

export default Statistics;
