import React, {PropTypes} from 'react';

const Statistics = ({positive, negative, neutral}) => {
    const min = Math.min(positive, negative, neutral);
    const max = Math.max(positive, negative, neutral);
    const getStyle = (number) => {
        const size = (((number - min) / (max - min)) * 75) + 75;
        return {
            fontSize: `${size}%`,
        };
    };
    return (
        <div className="statistics">
            <div className="positive" style={getStyle(positive)}>{positive}</div>
            <div className="negative" style={getStyle(negative)}>{negative}</div>
            <div className="neutral" style={getStyle(neutral)}>{neutral}</div>
        </div>
    );
};

Statistics.propTypes = {
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
};

Statistics.defaultProps = {
    positive: null,
    negative: null,
    neutral: null,
};

export default Statistics;
