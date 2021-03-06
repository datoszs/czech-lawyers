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
    select: () => dispatch(setGraphFilter(year)),
    deselect: () => dispatch(setGraphFilter()),
});

const mergeProps = ({selected, childSelected, positive, negative, neutral}, {select, deselect}, ownProps) => ({
    onClick: (positive || negative || neutral) && ((selected && !childSelected) ? deselect : select),
    selected,
    positive,
    negative,
    neutral,
    ...ownProps,
    BarComponent: Bar,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Year);
