import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {courtsMsg} from '../model';
import {CourtStatistics as StatisticsComponent} from '../components/statistics';
import translate from '../translate';
import {getStatistics} from './selectors';

const mapStateToProps = (state, {court}) => ({
    statistics: getStatistics(state, court),
    court: translate.getMessage(state, courtsMsg[court]),
});

const CourtStatistics = connect(mapStateToProps)(StatisticsComponent);

CourtStatistics.propTypes = {
    court: PropTypes.number.isRequired,
};

export default CourtStatistics;
