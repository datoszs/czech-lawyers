import React from 'react';
import PropTypes from 'prop-types';
import {Glyphicon, OverlayTrigger} from 'react-bootstrap';
import {Statistics} from '../../model';
import StatisticsColumn from './StatisticsColumn';
import styles from './BasicStatistics.css';

const BasicStatistics = ({statistics, legend}) => {
    const LegendComponent = legend;
    const max = Math.max(statistics.positive, statistics.negative, statistics.neutral);
    return (
        <OverlayTrigger placement="left" overlay={<LegendComponent />}>
            <h2 className={styles.main}>
                <StatisticsColumn max={max} number={statistics.positive || 0} type="positive" />
                <StatisticsColumn max={max} number={statistics.negative || 0} type="negative" />
                <StatisticsColumn max={max} number={statistics.neutral || 0} type="neutral" />
                <span className={styles.info}><Glyphicon glyph="question-sign" /></span>
            </h2>
        </OverlayTrigger>
    );
};

BasicStatistics.propTypes = {
    statistics: PropTypes.instanceOf(Statistics),
    legend: PropTypes.func.isRequired,
};

BasicStatistics.defaultProps = {
    statistics: new Statistics(),
};

export default BasicStatistics;
