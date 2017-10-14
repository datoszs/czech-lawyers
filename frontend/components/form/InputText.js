import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import styles from './InputText.less';

const InputText = ({input, placeholder}) => (
    <FormControl
        type="text"
        className={styles.main}
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
