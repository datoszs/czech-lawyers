import React, {PropTypes} from 'react';

const Statistics = ({positive, negative, neutral}) => {
    const max = Math.max(positive, negative, neutral);
    const style = (number) => ({fontSize: `${((number / max) * 75) + 75}%`});
    return (
        <h2 className="statistics">
            <div className="positive" style={style(positive)}>{positive}</div>
            <div className="negative" style={style(negative)}>{negative}</div>
            <div className="neutral" style={style(neutral)}>{neutral}</div>
        </h2>
    );
};

Statistics.propTypes = {
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
};

export default Statistics;
