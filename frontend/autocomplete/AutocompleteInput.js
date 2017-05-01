import {FormControl} from 'react-bootstrap';
import {connect} from 'react-redux';
import translate from '../translate';
import {getInputValue} from './selectors';
import {setInputValue} from './actions';

const mapStateToProps = (state) => ({
    placeholder: translate.getMessage(state, 'search.placeholder'),
    value: getInputValue(state),
});

const mapDispatchToProps = (dispatch) => ({
    onChange: (event) => dispatch(setInputValue(event.target.value)),
});

const mergeProps = (stateProps, dispatchProps) => ({
    ...stateProps,
    ...dispatchProps,
    type: 'text',
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(FormControl);
