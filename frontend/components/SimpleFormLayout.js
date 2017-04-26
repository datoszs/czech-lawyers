import React from 'react';
import PropTypes from 'prop-types';
import {Button} from 'react-bootstrap';

const SimpleFormLayout = ({bsStyle, submit, children}) => (
    <div className="simple-form">
        {children}
        <Button type="submit" bsStyle={bsStyle}>{submit}</Button>
    </div>
);

SimpleFormLayout.propTypes = {
    bsStyle: PropTypes.string,
    submit: PropTypes.node.isRequired,
    children: PropTypes.node.isRequired,
};

SimpleFormLayout.defaultProps = {
    bsStyle: null,
};

export default SimpleFormLayout;
