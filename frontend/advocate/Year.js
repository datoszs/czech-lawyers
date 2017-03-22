import {connect} from 'react-redux';
import {Year} from '../components/timeline';
import {getResults, getYearFilter, getResultFilter} from './selectors';
import {setGraphFilter} from './actions';
import Bar from './Bar';

const mapStateToProps = (state, {year}) => {
    const results = getResults(state, year);
    return {
        positive: results && results.positive,
        negative: results && results.negative,
        neutral: results && results.neutral,
        selected: getYearFilter(state) === year,
        childSelected: getResultFilter(state) !== null,
    };
};

const mapDispatchToProps = (dispatch, {year}) => ({
    onClick: () => dispatch(setGraphFilter(year)),
});

const mergeProps = ({selected, childSelected, ...stateProps}, {onClick}, ownProps) => ({
    onClick: selected && !childSelected ? () => {} : onClick,
    selected,
    ...stateProps,
    ...ownProps,
    BarComponent: Bar,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Year);
