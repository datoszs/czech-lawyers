import React from 'react';
import PropTypes from 'prop-types';
import {Statistics} from '../../model';
import StatisticsColumn from './StatisticsColumn';
import styles from './BigStatistics.less';

const BigStatistics = ({statistics, msgPositive, msgNegative, msgNeutral}) => {
    const max = Math.max(statistics.positive, statistics.negative, statistics.neutral);
    const createColumn = (property, legend) => (
        <StatisticsColumn scale={5} max={max} type={property} number={statistics[property]}>
            <div className={styles.text}>{legend}</div>
        </StatisticsColumn>
    );
    return (
        <div className={styles.main}>
            {createColumn('positive', msgPositive)}
            {createColumn('negative', msgNegative)}
            {createColumn('neutral', msgNeutral)}
        </div>
    );
};

BigStatistics.propTypes = {
    statistics: PropTypes.instanceOf(Statistics),
    msgPositive: PropTypes.string.isRequired,
    msgNegative: PropTypes.string.isRequired,
    msgNeutral: PropTypes.string.isRequired,
};

BigStatistics.defaultProps = {
    statistics: new Statistics(),
};

export default BigStatistics;
