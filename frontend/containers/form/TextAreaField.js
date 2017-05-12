import PropTypes from 'prop-types';
import {InputTextArea} from '../../components/form';
import BasicFieldComponent from './BasicFieldComponent';

const TextAreaField = BasicFieldComponent()(InputTextArea);

TextAreaField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string,
    required: PropTypes.bool,
};

TextAreaField.defaultProps = {
    label: null,
    required: false,
};

export default TextAreaField;
