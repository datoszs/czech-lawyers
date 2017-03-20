import React, {PropTypes} from 'react';

const BigStatistics = ({positive, negative, neutral, msgPositive, msgNegative, msgNeutral}) => {
    const max = Math.max(positive, negative, neutral);
    const style = (number) => ({fontSize: `${((number / max) * 75) + 75}%`});
    return (
        <div className="big-statistics">
            <div className="numbers">
                <div className="positive" style={style(positive)}>{positive}</div>
                <div className="negative" style={style(negative)}>{negative}</div>
                <div className="neutral" style={style(neutral)}>{neutral}</div>
            </div>
            <div className="legend">
                <div>{msgPositive}</div>
                <div>{msgNegative}</div>
                <div>{msgNeutral}</div>
            </div>
        </div>
    );
};

BigStatistics.propTypes = {
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
    msgPositive: PropTypes.string.isRequired,
    msgNegative: PropTypes.string.isRequired,
    msgNeutral: PropTypes.string.isRequired,
};

export default BigStatistics;
