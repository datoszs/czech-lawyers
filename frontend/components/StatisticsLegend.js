import React, {PropTypes} from 'react';

const StatisticsLegend = ({positive, negative, neutral}) => (
    <div>
        <ul className="statistics-legend">
            <li className="positive">{positive}</li>
            <li className="negative">{negative}</li>
            <li className="neutral">{neutral}</li>
        </ul>
    </div>
);

StatisticsLegend.propTypes = {
    positive: PropTypes.node.isRequired,
    negative: PropTypes.node.isRequired,
    neutral: PropTypes.node.isRequired,
};

export default StatisticsLegend;
