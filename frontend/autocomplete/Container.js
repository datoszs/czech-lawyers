import {compose} from 'redux';
import {connect} from 'react-redux';
import {transition} from '../util';
import advocateSearch from '../advocatesearch';
import translate from '../translate';
import Component from './Component';
import {setInputValue} from './actions';
import {getInputValue} from './selectors';

const mapStateToProps = (state) => ({
    value: getInputValue(state),
    msgPlaceholder: translate.getMessage(state, 'search.placeholder'),
    msgSearch: translate.getMessage(state, 'search.button'),
});

const mapDispatchToProps = (dispatch) => ({
    onChange: compose(dispatch, setInputValue),
});

const mergeProps = ({value, ...stateProps}, {...dispatchProps}) => ({
    value,
    ...stateProps,
    ...dispatchProps,
    onSubmit: () => transition(advocateSearch, undefined, {query: value}),
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Component);
