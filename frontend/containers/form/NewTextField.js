import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Field} from 'redux-form/immutable';
import translate from '../../translate';
import {SimpleInputText, BasicInputLayout} from '../../components/form';
import FormComponent from './FormComponent';

const mapStateToProps = (state, {label, placeholder}) => ({
    label: translate.getMessage(state, label),
    placeholder: placeholder && translate.getMessage(state, placeholder),
});

const mergeProps = ({label, placeholder}, dispatchProps, {name}) => ({
    name,
    label,
    placeholder,
    component: FormComponent(BasicInputLayout)(SimpleInputText),
});

const NewTextField = connect(mapStateToProps, undefined, mergeProps)(Field);

NewTextField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    placeholder: PropTypes.string,
};

NewTextField.defaultProps = {
    placeholder: null,
};

export default NewTextField;
