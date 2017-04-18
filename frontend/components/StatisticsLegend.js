import React, {PropTypes} from 'react';
import {Popover} from 'react-bootstrap';

const StatisticsLegend = ({positive, negative, neutral, placement, positionLeft, positionTop}) => (
    <Popover id="statistics-legend" placement={placement} positionLeft={positionLeft} positionTop={positionTop}>
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
    positionLeft: PropTypes.number.isRequired,
    positionTop: PropTypes.number.isRequired,
    placement: PropTypes.oneOf(['left', 'right', 'top', 'bottom']).isRequired,
};

export default StatisticsLegend;
