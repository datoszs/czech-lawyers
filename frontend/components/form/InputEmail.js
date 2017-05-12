import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';

const InputEmail = ({input, placeholder}) => (
    <FormControl
        type="email"
        className="input-email"
        placeholder={placeholder}
        value={input.value}
        onChange={input.onChange}
    />
);

InputEmail.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    placeholder: PropTypes.string,
};

InputEmail.defaultProps = {
    placeholder: '',
};

export default InputEmail;

