import {connect} from 'react-redux';
import {If} from '../util';
import {CurrentSearch} from '../components';
import translate from '../translate';
import {search} from './modules';

const mapStateToProps = (state) => ({
    legend: translate.getMessage(state, 'search.results'),
    query: search.getQuery(state),
});

const mergeProps = ({legend, query}) => ({
    test: !!query,
    Component: CurrentSearch,
    legend,
    query,
});

export default connect(mapStateToProps, undefined, mergeProps)(If);
