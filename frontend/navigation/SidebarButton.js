import React, {PropTypes} from 'react';
import {Button} from 'react-bootstrap';

const SidebarButton = ({children, ...rest}) => <Button bsSize="large" bsStyle="primary" {...rest}>{children}</Button>;

SidebarButton.propTypes = {
    children: PropTypes.node.isRequired,
};

export default SidebarButton;
