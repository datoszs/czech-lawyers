import {connect} from 'react-redux';
import {Field} from 'redux-form/immutable';
import translate from '../../translate';
import {BasicInputLayout} from '../../components/form';
import FormComponent from './FormComponent';

const baseMapStateToProps = (state, {label, placeholder}) => ({
    label: translate.getMessage(state, label),
    placeholder: placeholder && translate.getMessage(state, placeholder),
});

export default (mapStateToProps) => (Component) => {
    const mergeProps = (stateProps, dispatchProps, ownProps) => ({
        ...ownProps,
        ...stateProps,
        component: FormComponent(BasicInputLayout)(Component),
    });
    const finalMapStateToProps = !mapStateToProps ? baseMapStateToProps : (state, props) => {
        const baseProps = baseMapStateToProps(state, props);
        const stateProps = mapStateToProps(state, props);
        return ({...baseProps, ...stateProps});
    };
    return connect(finalMapStateToProps, undefined, mergeProps)(Field);
};
