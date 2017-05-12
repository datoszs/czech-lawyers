import React from 'react';
import PropTypes from 'prop-types';
import {FormGroup, ControlLabel, HelpBlock} from 'react-bootstrap';
import Required from './Required';

/**
 * Input layout with label at the top and validation message at the bottom.
 */
const BasicInputLayout = ({label, error, required, children}) => (
    <FormGroup validationState={error && 'error'}>
        <ControlLabel>{label}{required && <Required />}</ControlLabel>
        {children}
        {error && <HelpBlock>{error}</HelpBlock>}
    </FormGroup>
);

BasicInputLayout.propTypes = {
    label: PropTypes.string.isRequired,
    error: PropTypes.string,
    required: PropTypes.bool,
    children: PropTypes.node.isRequired,
};

BasicInputLayout.defaultProps = {
    error: null,
    required: false,
};

export default BasicInputLayout;
