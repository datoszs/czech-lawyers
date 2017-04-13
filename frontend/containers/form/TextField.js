import {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Field} from 'redux-form/immutable';
import translate from '../../translate';
import {InputText} from '../../components/form';

const mapStateToProps = (state, {label, placeholder}) => ({
    label: label && translate.getMessage(state, label),
    placeholder: placeholder && translate.getMessage(state, placeholder),
});

const mergeProps = ({label, placeholder}, dispatchProps, {name}) => ({
    component: InputText,
    label,
    placeholder,
    name,
});

const TextField = connect(mapStateToProps, undefined, mergeProps)(Field);

TextField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string,
    placeholder: PropTypes.string,
};

TextField.defaultProps = {
    label: null,
    placeholder: null,
};

export default TextField;
