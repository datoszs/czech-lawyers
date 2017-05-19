import React from 'react';
import PropTypes from 'prop-types';

const ChildrenDisplay = ({children}) => <div>{children}</div>;

ChildrenDisplay.propTypes = {
    children: PropTypes.node.isRequired,
};

export default ChildrenDisplay;
