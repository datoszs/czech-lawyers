import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {getError, isSuccess} from './selectors';
import {clear} from './actions';

const mapStateToProps = (state, {formName}) => {
    if (isSuccess(state, formName)) {
        return {
            bsStyle: 'success',
            children: 'Success!',
        };
    } else {
        return {
            bsStyle: 'danger',
            children: getError(state, formName),
        };
    }
};

const mapDispatchToProps = (dispatch, {formName}) => ({
    onDismiss: () => dispatch(clear(formName)),
});

const mergeProps = ({bsStyle, children}, {onDismiss}) => ({bsStyle, onDismiss, children});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Alert);
