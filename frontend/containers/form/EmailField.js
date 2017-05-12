import PropTypes from 'prop-types';
import {InputEmail} from '../../components/form';
import BasicFieldComponent from './BasicFieldComponent';


const EmailField = BasicFieldComponent()(InputEmail);

EmailField.propTypes = {
    label: PropTypes.string,
    placeholder: PropTypes.string,
    name: PropTypes.string.isRequired,
};

EmailField.defaultProps = {
    label: null,
    placeholder: null,
};

export default EmailField;
