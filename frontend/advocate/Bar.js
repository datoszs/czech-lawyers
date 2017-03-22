import {connect} from 'react-redux';
import {Bar} from '../components/timeline';
import {classNameResult} from '../model';
import {setGraphFilter} from './actions';
import {getYearFilter, getResultFilter} from './selectors';

const mapStateToProps = (state, {year, className}) => ({
    selected: year === getYearFilter(state) && classNameResult[className] === getResultFilter(state),
});

const mapDispatchToProps = (dispatch, {year, className}) => ({
    onClick: () => dispatch(setGraphFilter(year, classNameResult[className])),
});

const mergeProps = ({selected}, {onClick}, ownProps) => ({
    onClick: selected ? () => {} : onClick,
    selected,
    ...ownProps,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(Bar);
