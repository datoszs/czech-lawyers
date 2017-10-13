import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import styles from './InputTextArea.css';

const InputTextArea = ({input}) => (
    <FormControl
        componentClass="textarea"
        className={styles.main}
        value={input.value}
        onChange={input.onChange}
        onBlur={input.onBlur}
    />
);

InputTextArea.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
        onBlur: PropTypes.func.isRequired,
    }).isRequired,
};

export default InputTextArea;
