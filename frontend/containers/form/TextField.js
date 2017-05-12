import PropTypes from 'prop-types';
import {InputText} from '../../components/form';
import BasicFieldComponent from './BasicFieldComponent';

const NewTextField = BasicFieldComponent()(InputText);

NewTextField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    placeholder: PropTypes.string,
};

NewTextField.defaultProps = {
    placeholder: null,
};

export default NewTextField;
