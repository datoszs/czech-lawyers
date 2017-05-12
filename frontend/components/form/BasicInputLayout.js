import React from 'react';
import PropTypes from 'prop-types';
import {FormGroup, ControlLabel, HelpBlock} from 'react-bootstrap';

/**
 * Input layout with label at the top and validation message at the bottom.
 */
const BasicInputLayout = ({label, error, children}) => (
    <FormGroup validationState={error && 'error'}>
        <ControlLabel>{label}</ControlLabel>
        {children}
        {error && <HelpBlock>{error}</HelpBlock>}
    </FormGroup>
);

BasicInputLayout.propTypes = {
    label: PropTypes.string.isRequired,
    error: PropTypes.string,
    children: PropTypes.node.isRequired,
};

BasicInputLayout.defaultProps = {
    error: null,
};

export default BasicInputLayout;
