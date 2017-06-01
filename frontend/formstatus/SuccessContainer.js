import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import StatusContainer from './StatusContainer';
import {clearSuccess} from './actions';
import {isSuccess} from './selectors';

const mapStateToProps = (state, {formName, msg}) => ({
    msg: isSuccess(state, formName) ? msg : null,
});

const mapDispatchToProps = (dispatch, {formName}) => ({
    handleClear: () => dispatch(clearSuccess(formName)),
});

const mergeProps = ({msg}, {handleClear}, {formName}) => ({
    msg,
    formName,
    handleClear,
    bsStyle: 'success',
});

const SuccessContainer = connect(mapStateToProps, mapDispatchToProps, mergeProps)(StatusContainer);

SuccessContainer.propTypes = {
    msg: PropTypes.string.isRequired,
    formName: PropTypes.string.isRequired,
};

export default SuccessContainer;
