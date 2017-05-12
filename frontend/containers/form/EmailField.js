import PropTypes from 'prop-types';
import {InputEmail} from '../../components/form';
import BasicFieldComponent from './BasicFieldComponent';
import {isEmail} from './validations';

const EmailField = BasicFieldComponent(undefined, {validate: isEmail})(InputEmail);

EmailField.propTypes = {
    label: PropTypes.string,
    placeholder: PropTypes.string,
    name: PropTypes.string.isRequired,
    required: PropTypes.bool,
};

EmailField.defaultProps = {
    label: null,
    placeholder: null,
    required: false,
};

export default EmailField;
