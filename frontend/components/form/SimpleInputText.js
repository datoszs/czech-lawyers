import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';

const SimpleInputText = ({input, placeholder}) => (
    <FormControl
        type="text"
        placeholder={placeholder}
        value={input.value}
        onChange={input.onChange}
    />
);

SimpleInputText.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    placeholder: PropTypes.string,
};

SimpleInputText.defaultProps = {
    placeholder: '',
};

export default SimpleInputText;
