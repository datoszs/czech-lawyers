import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import StatusContainer from './StatusContainer';
import {isSuccess} from './selectors';

const mapStateToProps = (state, {formName, msg}) => ({
    msg: isSuccess(state, formName) ? msg : null,
});

const mergeProps = ({msg}, dispatchProps, {formName}) => ({
    msg,
    formName,
    bsStyle: 'success',
});

const SuccessContainer = connect(mapStateToProps, undefined, mergeProps)(StatusContainer);

SuccessContainer.propTypes = {
    msg: PropTypes.string.isRequired,
    formName: PropTypes.string.isRequired,
};

export default SuccessContainer;
