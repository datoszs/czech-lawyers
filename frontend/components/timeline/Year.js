import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import styles from './Year.less';

const Year = ({year, positive, negative, neutral, BarComponent, onClick, selected}) => (
    <div
        className={classnames({
            [styles.main]: true,
            [styles.selected]: selected,
        })}
        onClick={onClick}
    >
        {positive && <BarComponent className="positive" year={year} size={positive} />}
        {negative && <BarComponent className="negative" year={year} size={negative} />}
        {neutral && <BarComponent className="neutral" year={year} size={neutral} />}
        <div className={styles.legend}>{year}</div>
    </div>
);

Year.defaultProps = {
    positive: null,
    negative: null,
    neutral: null,
    selected: false,
    onClick: () => {},
};

Year.propTypes = {
    year: PropTypes.number.isRequired,
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
    BarComponent: PropTypes.func.isRequired,
    selected: PropTypes.bool,
    onClick: PropTypes.func,
};

export default Year;
