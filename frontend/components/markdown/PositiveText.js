import React, {PropTypes} from 'react';

const PositiveText = ({text}) => <span className="text-positive">{text}</span>;

PositiveText.propTypes = {
    text: PropTypes.string.isRequired,
};

export default PositiveText;
