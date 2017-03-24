import React, {PropTypes} from 'react';
import classnames from 'classnames';

const Year = ({year, positive, negative, neutral, BarComponent, onClick, selected}) => (
    <div
        className={classnames({
            'timeline-year': true,
            selected,
        })}
        onClick={onClick}
    >
        {positive && <BarComponent className="positive" year={year} size={positive} />}
        {negative && <BarComponent className="negative" year={year} size={negative} />}
        {neutral && <BarComponent className="neutral" year={year} size={neutral} />}
        <div className="legend">{year}</div>
    </div>
);

Year.defaultProps = {
    positive: null,
    negative: null,
    neutral: null,
    selected: false,
};

Year.propTypes = {
    year: PropTypes.number.isRequired,
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
    BarComponent: PropTypes.func.isRequired,
    selected: PropTypes.bool,
    onClick: PropTypes.func.isRequired,
};

export default Year;
