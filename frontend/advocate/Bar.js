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
    onClick: () => dispatch(setGraphFilter(year, classNameResult[className])),
});

const mergeProps = ({selected, size}, {onClick}, ownProps) => ({
    ...ownProps,
    onClick: selected ? () => {} : onClick,
    selected,
    size,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Bar);
