import React, {PropTypes} from 'react';

const Statistics = ({positive, negative, neutral}) => {
    const max = Math.max(positive, negative, neutral);
    const bar = (number) => (
        <div
            className="bar"
            style={{
                height: `${(number / max) * 150}%`,
            }}
        />
    );
    return (
        <div className="statistics">
            <div className="positive">{positive}{bar(positive)}</div>
            <div className="negative">{negative}{bar(negative)}</div>
            <div className="neutral">{neutral}{bar(neutral)}</div>
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
