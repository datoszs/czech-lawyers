import React from 'react';
import PropTypes from 'prop-types';
import {Button} from 'react-bootstrap';

const SidebarButton = ({children, ...rest}) => <Button bsSize="large" bsStyle="primary" {...rest}>{children}</Button>;

SidebarButton.propTypes = {
    children: PropTypes.node.isRequired,
};

export default SidebarButton;
