import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {getError} from './selectors';
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

const mergeProps = ({msg}, dispatchProps, {formName}) => ({
    msg,
    formName,
    bsStyle: 'danger',
});

const ErrorContainer = connect(mapStateToProps, undefined, mergeProps)(StatusContainer);

ErrorContainer.propTypes = {
    formName: PropTypes.string.isRequired,
    defaultMsg: PropTypes.string.isRequired,
    errorMap: PropTypes.objectOf(PropTypes.string.isRequired),
};

ErrorContainer.defaultProps = {
    errorMap: {},
};

export default ErrorContainer;
