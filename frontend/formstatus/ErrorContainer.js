import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {LifecycleListener, If} from '../util';
import translate from '../translate';
import {getError} from './selectors';
import {clearError} from './actions';

const mapStateToProps = (state, {formName, errorMap, defaultMsg}) => {
    const error = getError(state, formName);
    if (error) {
        const msg = errorMap[error];
        if (msg) {
            return {msg: translate.getMessage(state, msg)};
        } else {
            return {msg: translate.getMessage(state, defaultMsg)};
        }
    } else {
        return {msg: null};
    }
};

const mapDispatchToProps = (dispatch, {formName}) => ({
    handleClear: () => dispatch(clearError(formName)),
});

const mergeProps = ({msg}, {handleClear}) => ({
    children: msg,
    onDismiss: handleClear,
    onUnmount: handleClear,
    bsStyle: 'danger',
    test: !!msg,
    Component: LifecycleListener(Alert),
});

const ErrorContainer = connect(mapStateToProps, mapDispatchToProps, mergeProps)(If);

ErrorContainer.propTypes = {
    formName: PropTypes.string.isRequired,
    defaultMsg: PropTypes.string.isRequired,
    errorMap: PropTypes.objectOf(PropTypes.string.isRequired),
};

ErrorContainer.defaultProps = {
    errorMap: {},
};

export default ErrorContainer;
