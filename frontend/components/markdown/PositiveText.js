import React from 'react';
import PropTypes from 'prop-types';

const PositiveText = ({text}) => <span className="text-positive">{text}</span>;

PositiveText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default PositiveText;
