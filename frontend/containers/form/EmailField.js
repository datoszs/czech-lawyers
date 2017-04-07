import {PropTypes} from 'react';
import {Field} from 'redux-form/immutable';
import {connect} from 'react-redux';
import translate from '../../translate';
import {InputEmail} from '../../components/form';

const mapStateToProps = (state, {label, placeholder}) => ({
    label: label && translate.getMessage(state, label),
    placeholder: placeholder && translate.getMessage(state, placeholder),
});

const mergeProps = ({label, placeholder}, dispatchProps, {name}) => ({
    component: InputEmail,
    props: {
        label,
        placeholder,
    },
    name,
});

const EmailField = connect(mapStateToProps, undefined, mergeProps)(Field);

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
