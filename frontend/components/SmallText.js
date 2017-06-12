import React from 'react';
import PropTypes from 'prop-types';

const SmallText = ({children}) => <div className="text-small">{children}</div>;

SmallText.propTypes = {
    children: PropTypes.node.isRequired,
};

export default SmallText;
