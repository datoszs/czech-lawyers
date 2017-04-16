import React, {PropTypes} from 'react';
import {FormGroup, FormControl, ControlLabel} from 'react-bootstrap';

const InputText = ({input, label, placeholder}) => (
    <FormGroup>
        {label && <ControlLabel>{label}</ControlLabel>}
        <FormControl
            type="text"
            className="input-text"
            placeholder={placeholder}
            value={input.value}
            onChange={input.onChange}
        />
    </FormGroup>
);

InputText.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    label: PropTypes.string,
    placeholder: PropTypes.string,
};

InputText.defaultProps = {
    label: null,
    placeholder: '',
};

export default InputText;
