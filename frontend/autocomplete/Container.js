import {connect} from 'react-redux';
import {transition} from '../util';
import advocateSearch from '../advocatesearch';
import translate from '../translate';
import Component from './Component';
import {getInputValue} from './selectors';

const mapStateToProps = (state) => ({
    value: getInputValue(state),
    msgSearch: translate.getMessage(state, 'search.button'),
});

const mergeProps = ({value, msgSearch}) => ({
    value,
    msgSearch,
    onSubmit: () => transition(advocateSearch.ROUTE, undefined, {query: value}),
});

export default connect(mapStateToProps, undefined, mergeProps)(Component);
