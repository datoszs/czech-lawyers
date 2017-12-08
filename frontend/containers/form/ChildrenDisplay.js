import React from 'react';
import PropTypes from 'prop-types';
import BasicFieldComponent from './BasicFieldComponent';

const ChildrenDisplay = ({children}) => <div>{children}</div>;

ChildrenDisplay.propTypes = {
    children: PropTypes.node.isRequired,
};

export default BasicFieldComponent()(ChildrenDisplay);
