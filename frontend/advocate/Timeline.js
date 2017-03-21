import {connect} from 'react-redux';
import {Timeline} from '../components/timeline';
import {getStartYear} from './selectors';
import Year from './Year';

const mapStateToProps = (state) => ({
    startYear: getStartYear(state),
});

const mapDispatchToProps = () => ({
    YearComponent: Year,
});

export default connect(mapStateToProps, mapDispatchToProps)(Timeline);
