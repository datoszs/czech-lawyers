import React from 'react';
import PropTypes from 'prop-types';

const NegativeText = ({text}) => <span className="text-negative">{text}</span>;

NegativeText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default NegativeText;
