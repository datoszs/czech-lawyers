import React from 'react';
import PropTypes from 'prop-types';
import {sequence, getCurrentYear} from '../../util';
import styles from './Timeline.less';

const Timeline = ({YearComponent, startYear}) => (
    <div className={styles.main}>
        {
            sequence((getCurrentYear() - startYear) + 1)
                .map((year) => year + startYear)
                .map((year) => <YearComponent key={year} year={year} />)
        }

    </div>
);

Timeline.defaultProps = {
    startYear: getCurrentYear(),
};

Timeline.propTypes = {
    YearComponent: PropTypes.func.isRequired,
    startYear: PropTypes.number,
};

export default Timeline;
