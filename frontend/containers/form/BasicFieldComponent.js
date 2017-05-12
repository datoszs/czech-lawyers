import {connect} from 'react-redux';
import {Field} from 'redux-form/immutable';
import translate from '../../translate';
import {BasicInputLayout} from '../../components/form';
import FormComponent from './FormComponent';
import {isRequired} from './validations';

const baseMapStateToProps = (state, {label, placeholder}) => ({
    label: translate.getMessage(state, label),
    placeholder: placeholder && translate.getMessage(state, placeholder),
});

const createValidate = (options) => (required) => {
    const result = [];
    if (required) {
        result.push(isRequired);
    }
    if (options && options.validate) {
        result.push(options.validate);
    }
    return result;
};

export default (mapStateToProps, options) => (Component) => {
    const validate = createValidate(options);
    const mergeProps = (stateProps, dispatchProps, {required, ...ownProps}) => ({
        ...ownProps,
        ...stateProps,
        required,
        validate: validate(required),
        component: FormComponent(BasicInputLayout)(Component),
    });
    const finalMapStateToProps = !mapStateToProps ? baseMapStateToProps : (state, props) => {
        const baseProps = baseMapStateToProps(state, props);
        const stateProps = mapStateToProps(state, props);
        return ({...baseProps, ...stateProps});
    };
    return connect(finalMapStateToProps, undefined, mergeProps)(Field);
};
