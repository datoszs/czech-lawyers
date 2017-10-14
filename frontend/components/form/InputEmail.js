import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import styles from './InputEmail.less';

const InputEmail = ({input, placeholder}) => (
    <FormControl
        type="email"
        className={styles.main}
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

