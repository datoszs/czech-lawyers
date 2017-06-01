import React from 'react';
import PropTypes from 'prop-types';
import {Button} from 'react-bootstrap';
import Spinner from 'react-spinkit';

const SubmitButton = ({bsStyle, children, disabled, submitting}) => (
    <Button
        disabled={disabled || submitting}
        bsStyle={bsStyle}
        type="submit"
        className="submit-button"
    >
        {children}
        {submitting && <Spinner name="circle" className="submit-spinner" fadeIn="none" />}
    </Button>
);

SubmitButton.propTypes = {
    bsStyle: PropTypes.oneOf(['primary', 'danger']),
    children: PropTypes.node.isRequired,
    disabled: PropTypes.bool,
    submitting: PropTypes.bool,
};

SubmitButton.defaultProps = {
    bsStyle: 'primary',
    disabled: false,
    submitting: false,
};

export default SubmitButton;
