import PropTypes from 'prop-types';
import {InputText} from '../../components/form';
import BasicFieldComponent from './BasicFieldComponent';

const NewTextField = BasicFieldComponent()(InputText);

NewTextField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    placeholder: PropTypes.string,
    required: PropTypes.bool,
};

NewTextField.defaultProps = {
    placeholder: null,
    required: false,
};

export default NewTextField;
