import {connect} from 'react-redux';
import {Year} from '../components/timeline';
import {getResults} from './selectors';
import Bar from './Bar';

const mapStateToProps = (state, {year}) => {
    const results = getResults(state, year);
    return {
        positive: results && results.positive,
        negative: results && results.negative,
        neutral: results && results.neutral,
    };
};

const mapDispatchToProps = (dispatch) => ({
    BarComponent: Bar,
});

export default connect(mapStateToProps, mapDispatchToProps)(Year);
