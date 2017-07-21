import {connect} from 'react-redux';
import {If} from '../util';
import {SearchDisclaimer} from '../containers';
import {search} from './modules';

const mapStateToProps = (state) => ({
    hasQuery: !!search.getQuery(state),
});

const mergeProps = ({hasQuery}) => ({
    test: hasQuery,
    Component: SearchDisclaimer,
});

export default connect(mapStateToProps, undefined, mergeProps)(If);
