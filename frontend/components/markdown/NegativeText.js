import React, {PropTypes} from 'react';

const NegativeText = ({text}) => <span className="text-negative">{text}</span>;

NegativeText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default NegativeText;
