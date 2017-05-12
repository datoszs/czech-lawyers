import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';

const InputText = ({input, placeholder}) => (
    <FormControl
        type="text"
        placeholder={placeholder}
        value={input.value}
        onChange={input.onChange}
        onBlur={input.onBlur}
    />
);

InputText.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
        onBlur: PropTypes.func.isRequired,
    }).isRequired,
    placeholder: PropTypes.string,
};

InputText.defaultProps = {
    placeholder: '',
};

export default InputText;
