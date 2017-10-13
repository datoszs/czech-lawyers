import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import styles from './index.css';

const InputEmail = ({input, placeholder}) => (
    <FormControl
        type="email"
        className={styles.email}
        placeholder={placeholder}
        value={input.value}
        onChange={input.onChange}
        onBlur={input.onBlur}
    />
);

InputEmail.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
        onBlur: PropTypes.func.isRequired,
    }).isRequired,
    placeholder: PropTypes.string,
};

InputEmail.defaultProps = {
    placeholder: '',
};

export default InputEmail;

