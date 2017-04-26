import React from 'react';
import PropTypes from 'prop-types';

/**
 * Simple text component.
 */
const Text = ({text}) => <span>{text}</span>;

Text.propTypes = {
    text: PropTypes.string.isRequired,
};

export default Text;
