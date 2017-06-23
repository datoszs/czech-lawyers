import React from 'react';
import PropTypes from 'prop-types';
import {Statistics} from '../../model';
import StatisticsColumn from './StatisticsColumn';
import StatisticsBar from './StatisticsBar';

const statMax = (statistics) => Math.max(statistics.positive, statistics.negative, statistics.neutral);

const CourtStatistics = ({statistics, courtStatistics, court}) => {
    const max = statMax(statistics);
    const courtMax = courtStatistics && statMax(courtStatistics);

    const createColumn = (property) => (
        <StatisticsColumn max={max} number={statistics[property] || 0} type={property}>
            {courtStatistics && <div className="statistics-divider" />}
            {courtStatistics && <StatisticsBar max={courtMax} number={courtStatistics[property]} type={`${property}-court`} />}
        </StatisticsColumn>
    );

    return (
        <div className="statistics-court">
            <h2 className="statistics-court-row">
                {createColumn('positive')}
                {createColumn('negative')}
                {createColumn('neutral')}
            </h2>
            <div className="statistics-court-header">{court}</div>
        </div>
    );
};

CourtStatistics.propTypes = {
    statistics: PropTypes.instanceOf(Statistics),
    courtStatistics: PropTypes.instanceOf(Statistics),
    court: PropTypes.string,
};

CourtStatistics.defaultProps = {
    statistics: new Statistics(),
    courtStatistics: null,
    court: '',
};

export default CourtStatistics;
