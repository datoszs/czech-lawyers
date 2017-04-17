import React, {PropTypes} from 'react';
import {Popover} from 'react-bootstrap';

const StatisticsLegend = ({positive, negative, neutral, positionLeft, positionTop}) => (
    <Popover id="statistics-legend" placement="right" positionLeft={positionLeft} positionTop={positionTop}>
        <ul className="statistics-legend">
            <li className="positive">{positive}</li>
            <li className="negative">{negative}</li>
            <li className="neutral">{neutral}</li>
        </ul>
    </Popover>
);

StatisticsLegend.propTypes = {
    positive: PropTypes.node.isRequired,
    negative: PropTypes.node.isRequired,
    neutral: PropTypes.node.isRequired,
};

export default StatisticsLegend;
