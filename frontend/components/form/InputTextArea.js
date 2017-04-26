import React from 'react';
import PropTypes from 'prop-types';
import {FormGroup, FormControl, ControlLabel} from 'react-bootstrap';

const InputTextArea = ({input, label}) => (
    <FormGroup>
        {label && <ControlLabel>{label}</ControlLabel>}
        <FormControl
            componentClass="textarea"
            className="input-text-area"
            value={input.value}
            onChange={input.onChange}
        />
    </FormGroup>
);

InputTextArea.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    label: PropTypes.string,
};

InputTextArea.defaultProps = {
    label: null,
};

export default InputTextArea;
