import React from 'react';
import PropTypes from 'prop-types';
import {Glyphicon, OverlayTrigger} from 'react-bootstrap';
import StatisticsBar from './StatisticsBar';

const Statistics = ({positive, negative, neutral, legend}) => {
    const max = Math.max(positive, negative, neutral);
    const LegendComponent = legend;
    if (legend) {
        return (
            <OverlayTrigger placement="left" overlay={<LegendComponent />}>
                <h2 className="statistics">
                    <StatisticsBar number={positive} max={max} type="positive" />
                    <StatisticsBar number={negative} max={max} type="negative" />
                    <StatisticsBar number={neutral} max={max} type="neutral" />
                    <span className="info"><Glyphicon glyph="question-sign" /></span>
                </h2>
            </OverlayTrigger>
        );
    } else {
        return (
            <h2 className="statistics">
                <StatisticsBar number={positive} max={max} type="positive" />
                <StatisticsBar number={negative} max={max} type="negative" />
                <StatisticsBar number={neutral} max={max} type="neutral" />
            </h2>
        );
    }
};

Statistics.propTypes = {
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
    legend: PropTypes.func,
};

Statistics.defaultProps = {
    legend: null,
};

export default Statistics;
