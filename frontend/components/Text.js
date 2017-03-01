import React, {PropTypes} from 'react';

/**
 * Simple text component.
 */
const Text = ({text}) => <span>{text}</span>;

Text.propTypes = {
    text: PropTypes.string.isRequired,
};

export default Text;
