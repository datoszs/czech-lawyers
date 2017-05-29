import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {LifecycleListener} from '../util';
import translate from '../translate';
import {clear} from './actions';

const mapStateToProps = (state, {msg}) => ({
    children: translate.getMessage(state, msg),
});

const mapDispatchToProps = (dispatch, {formName}) => {
    const doClear = () => dispatch(clear(formName));
    return {
        onDismiss: doClear,
        onUnmount: doClear,
    };
};

const AlertContainer = connect(mapStateToProps, mapDispatchToProps)(LifecycleListener(Alert));
AlertContainer.propTypes = {
    formName: PropTypes.string.isRequired,
    msg: PropTypes.string.isRequired,
    bsStyle: PropTypes.string.isRequired,
};

export default AlertContainer;
