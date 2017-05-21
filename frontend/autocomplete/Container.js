import {compose} from 'redux';
import {connect} from 'react-redux';
import {LifecycleListener} from '../util';
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
    onUnmount: compose(dispatch, hideDropdown),
});

export default connect(mapStateToProps, mapDispatchToProps)(LifecycleListener(Component));
