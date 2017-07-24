import {connect} from 'react-redux';
import {If} from '../util';
import {SearchDisclaimer} from '../containers';
import {search} from './modules';

const mapStateToProps = (state) => ({
    hasResults: !!search.getQuery(state) && search.getCount(state) > 0,
});

const mergeProps = ({hasResults}) => ({
    test: hasResults,
    Component: SearchDisclaimer,
});

export default connect(mapStateToProps, undefined, mergeProps)(If);
