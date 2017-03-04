import React, {PropTypes} from 'react';

const Statistics = ({number, scale, color}) => (
    <h1
        style={{
            color,
            display: 'inline-block',
            transform: `scale(${scale}) translateY(-${scale * 10}%)`,
            margin: 7,
            verticalAlign: 'top',
        }}
    >
        {number}
    </h1>
);

Statistics.propTypes = {
    number: PropTypes.number.isRequired,
    scale: PropTypes.number.isRequired,
    color: PropTypes.string.isRequired,
};

export default Statistics;
