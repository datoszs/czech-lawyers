import {connect} from 'react-redux';
import {Bar} from '../components/timeline';
import {classNameResult} from '../model';
import {setGraphFilter} from './actions';
import {getYearFilter, getResultFilter, getMaxCases} from './selectors';

const mapStateToProps = (state, {year, className, size}) => ({
    selected: year === getYearFilter(state) && classNameResult[className] === getResultFilter(state),
    size: size / getMaxCases(state),
});

const mapDispatchToProps = (dispatch, {year, className}) => ({
    select: () => dispatch(setGraphFilter(year, classNameResult[className])),
    deselect: () => dispatch(setGraphFilter()),
});

const mergeProps = ({selected, size}, {select, deselect}, ownProps) => ({
    ...ownProps,
    onClick: selected ? deselect : select,
    selected,
    size,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Bar);
