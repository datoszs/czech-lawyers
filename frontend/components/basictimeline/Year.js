import React, {PropTypes} from 'react';
import {connect} from 'react-redux';

const YearComponent = ({year, positive, negative, neutral}) => (
    <div className="year">
        {positive && <div className="positive" style={{height: `${positive}ex`}} />}
        {negative && <div className="negative" style={{height: `${negative}ex`}} />}
        {neutral && <div className="neutral" style={{height: `${neutral}ex`}} />}
        <div className="legend">{year}</div>
    </div>
);

YearComponent.defaultProps = {
    positive: 0,
    negative: 0,
    neutral: 0,
};

YearComponent.propTypes = {
    year: PropTypes.number.isRequired,
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
};

const mapStateToProps = (state, {positiveSelector, negativeSelector, neutralSelector, year}) => ({
    positive: positiveSelector(state, year),
    negative: negativeSelector(state, year),
    neutral: neutralSelector(state, year),
});

const Year = connect(mapStateToProps)(YearComponent);

Year.propTypes = {
    year: PropTypes.number.isRequired,
    positiveSelector: PropTypes.func.isRequired,
    negativeSelector: PropTypes.func.isRequired,
    neutralSelector: PropTypes.func.isRequired,
};

export default Year;
