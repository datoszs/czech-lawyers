import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {LifecycleListener, If} from '../util';
import translate from '../translate';
import {clearSuccess} from './actions';
import {isSuccess} from './selectors';

const mapStateToProps = (state, {formName, msg}) => ({
    msg: isSuccess(state, formName) ? translate.getMessage(state, msg) : null,
});

const mapDispatchToProps = (dispatch, {formName}) => ({
    handleClear: () => dispatch(clearSuccess(formName)),
});

const mergeProps = ({msg}, {handleClear}) => ({
    children: msg,
    onDismiss: handleClear,
    onUnmount: handleClear,
    bsStyle: 'success',
    test: !!msg,
    Component: LifecycleListener(Alert),
});

const SuccessContainer = connect(mapStateToProps, mapDispatchToProps, mergeProps)(If);

SuccessContainer.propTypes = {
    msg: PropTypes.string.isRequired,
    formName: PropTypes.string.isRequired,
};

export default SuccessContainer;
