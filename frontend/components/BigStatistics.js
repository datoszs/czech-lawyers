import React from 'react';
import PropTypes from 'prop-types';
import StatisticsBar from './StatisticsBar';

const BigStatistics = ({positive, negative, neutral, msgPositive, msgNegative, msgNeutral}) => {
    const max = Math.max(positive, negative, neutral);
    return (
        <div className="big-statistics">
            <div>
                <div className="big-bar">
                    <StatisticsBar number={positive} max={max} type="positive" />
                </div>
                <div>{msgPositive}</div>
            </div>
            <div>
                <div className="big-bar">
                    <StatisticsBar number={negative} max={max} type="negative" />
                </div>
                <div>{msgNegative}</div>
            </div>
            <div>
                <div className="big-bar">
                    <StatisticsBar number={neutral} max={max} type="neutral" />
                </div>
                <div>{msgNeutral}</div>
            </div>
        </div>
    );
};

BigStatistics.propTypes = {
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
    msgPositive: PropTypes.string.isRequired,
    msgNegative: PropTypes.string.isRequired,
    msgNeutral: PropTypes.string.isRequired,
};

export default BigStatistics;
