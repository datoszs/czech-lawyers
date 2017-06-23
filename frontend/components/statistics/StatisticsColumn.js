import React from 'react';
import PropTypes from 'prop-types';
import StatisticsBar from './StatisticsBar';

const StatisticsColumn = ({number, max, scale, type, children}) => (
    <div className={`statistics-column-${type}`}>
        <div
            className="statistics-column-top"
            style={{fontSize: `${scale}em`}}
        >
            <div className="statistics-column-text">{number}</div>
            <div className="statistics-column-bar">
                <StatisticsBar number={number} max={max} type={type} />
            </div>
        </div>
        {children}
    </div>
);

StatisticsColumn.propTypes = {
    number: PropTypes.number,
    max: PropTypes.number.isRequired,
    scale: PropTypes.number,
    type: PropTypes.oneOf(['positive', 'negative', 'neutral']).isRequired,
    children: PropTypes.node,
};

StatisticsColumn.defaultProps = {
    number: 0,
    scale: 1,
    children: null,
};

export default StatisticsColumn;
