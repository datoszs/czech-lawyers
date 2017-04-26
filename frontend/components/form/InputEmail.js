import React from 'react';
import PropTypes from 'prop-types';
import {FormGroup, FormControl, ControlLabel} from 'react-bootstrap';

const InputEmail = ({input, label, placeholder}) => (
    <FormGroup>
        {label && <ControlLabel>{label}</ControlLabel>}
        <FormControl
            type="email"
            className="input-email"
            placeholder={placeholder}
            value={input.value}
            onChange={input.onChange}
        />
    </FormGroup>
);

InputEmail.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    label: PropTypes.string,
    placeholder: PropTypes.string,
};

InputEmail.defaultProps = {
    label: null,
    placeholder: '',
};

export default InputEmail;

