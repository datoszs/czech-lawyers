import {compose} from 'redux';
import {connect} from 'react-redux';
import translate from '../translate';
import Component from './Component';
import {submit, hideDropdown} from './actions';
import {getInputValue} from './selectors';

const mapStateToProps = (state) => ({
    value: getInputValue(state),
    msgSearch: translate.getMessage(state, 'search.button'),
});

const mapDispatchToProps = (dispatch) => ({
    onSubmit: compose(dispatch, submit),
    hide: compose(dispatch, hideDropdown),
});

export default connect(mapStateToProps, mapDispatchToProps)(Component);
