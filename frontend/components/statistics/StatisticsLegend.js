import React from 'react';
import PropTypes from 'prop-types';
import {Popover} from 'react-bootstrap';
import styles from './StatisticsLegend.less';

const StatisticsLegend = ({positive, negative, neutral, placement, positionLeft, positionTop}) => (
    <Popover id="statistics-legend" placement={placement} positionLeft={positionLeft} positionTop={positionTop}>
        <ul className={styles.list}>
            <li className={styles.positive}>{positive}</li>
            <li className={styles.negative}>{negative}</li>
            <li className={styles.neutral}>{neutral}</li>
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
