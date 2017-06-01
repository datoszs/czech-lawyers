import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {getError} from './selectors';
import {clearError} from './actions';
import StatusContainer from './StatusContainer';

const mapStateToProps = (state, {formName, errorMap, defaultMsg}) => {
    const error = getError(state, formName);
    if (error) {
        const msg = errorMap[error];
        if (msg) {
            return {msg};
        } else {
            return {msg: defaultMsg};
        }
    } else {
        return {msg: null};
    }
};

const mapDispatchToProps = (dispatch, {formName}) => ({
    handleClear: () => dispatch(clearError(formName)),
});

const mergeProps = ({msg}, {handleClear}, {formName}) => ({
    msg,
    formName,
    handleClear,
    bsStyle: 'danger',
});

const ErrorContainer = connect(mapStateToProps, mapDispatchToProps, mergeProps)(StatusContainer);

ErrorContainer.propTypes = {
    formName: PropTypes.string.isRequired,
    defaultMsg: PropTypes.string.isRequired,
    errorMap: PropTypes.objectOf(PropTypes.string.isRequired),
};

ErrorContainer.defaultProps = {
    errorMap: {},
};

export default ErrorContainer;
