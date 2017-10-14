import React from 'react';
import PropTypes from 'prop-types';
import StatisticsBar from './StatisticsBar';
import styles from './StatisticsColumn.less';

const textStyle = {
    positive: styles.textPositive,
    negative: styles.textNegative,
    neutral: styles.textNeutral,
};

const barStyle = {
    positive: styles.barPositive,
    negative: styles.barNegative,
    neutral: styles.barNeutral,
}

const StatisticsColumn = ({number, max, scale, type, children}) => (
    <div className={styles.main}>
        <div
            className={styles.top}
            style={{fontSize: `${scale}em`}}
        >
            <div className={textStyle[type]}>{number}</div>
            <div className={styles.bar}>
                <StatisticsBar number={number} max={max} className={barStyle[type]} />
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
