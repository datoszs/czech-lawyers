import {connect} from 'react-redux';
import {Timeline} from '../components';
import {getStartYear, getPositive, getNegative, getNeutral} from './selectors';

const mapStateToProps = (state) => ({
    startYear: getStartYear(state),
});

const mergeProps = ({startYear}) => ({
    startYear,
    positiveSelector: getPositive,
    negativeSelector: getNegative,
    neutralSelector: getNeutral,
});

export default connect(mapStateToProps, undefined, mergeProps)(Timeline);
