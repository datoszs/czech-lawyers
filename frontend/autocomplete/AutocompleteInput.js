import {FormControl} from 'react-bootstrap';
import {connect} from 'react-redux';
import translate from '../translate';
import {getInputValue} from './selectors';
import {setInputValue, hideDropdown, showDropdown, moveSelectionUp, moveSelectionDown} from './actions';

const mapStateToProps = (state) => ({
    placeholder: translate.getMessage(state, 'search.placeholder'),
    value: getInputValue(state),
});

const handleKeyDown = (dispatch) => (event) => {
    switch (event.key) {
        case 'Escape':
            dispatch(hideDropdown());
            break;
        case 'ArrowDown':
            dispatch(moveSelectionDown());
            break;
        case 'ArrowUp':
            dispatch(moveSelectionUp());
            break;
        default:
            // do nothing
            break;
    }
};

const mapDispatchToProps = (dispatch) => ({
    onChange: (event) => dispatch(setInputValue(event.target.value)),
    onKeyDown: handleKeyDown(dispatch),
    onBlur: () => dispatch(hideDropdown()),
    onFocus: () => dispatch(showDropdown()),
});

const mergeProps = (stateProps, dispatchProps) => ({
    ...stateProps,
    ...dispatchProps,
    type: 'text',
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(FormControl);
