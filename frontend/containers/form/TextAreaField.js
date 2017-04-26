import PropTypes from 'prop-types';
import {Field} from 'redux-form/immutable';
import {connect} from 'react-redux';
import translate from '../../translate';
import {InputTextArea} from '../../components/form';

const mapStateToProps = (state, {label}) => ({
    label: label && translate.getMessage(state, label),
});

const mergeProps = ({label}, dispatchProps, {name}) => ({
    component: InputTextArea,
    props: {label},
    name,
});

const TextAreaField = connect(mapStateToProps, undefined, mergeProps)(Field);

TextAreaField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string,
};

TextAreaField.defaultProps = {
    label: null,
};

export default TextAreaField;
