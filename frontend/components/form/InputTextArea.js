import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';

const InputTextArea = ({input}) => (
    <FormControl
        componentClass="textarea"
        className="input-text-area"
        value={input.value}
        onChange={input.onChange}
    />
);

InputTextArea.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
};

export default InputTextArea;
