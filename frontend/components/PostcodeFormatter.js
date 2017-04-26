import React from 'react';
import PropTypes from 'prop-types';

const PostcodeFormatter = ({value}) => (
    <span>{value.substring(0, 3)}&nbsp;{value.substring(3, 5)}</span>
);

PostcodeFormatter.propTypes = {
    value: PropTypes.string.isRequired,
};

export default PostcodeFormatter;
