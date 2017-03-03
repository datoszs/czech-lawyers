import React, {PropTypes} from 'react';

const Statistics = ({number, scale, color}) => (
    <h2
        style={{
            color,
            display: 'inline-block',
            transform: `scale(${scale})`,
            margin: 2,
        }}
    >
        {number}
    </h2>
);

Statistics.propTypes = {
    number: PropTypes.number.isRequired,
    scale: PropTypes.number.isRequired,
    color: PropTypes.string.isRequired,
};

export default Statistics;
