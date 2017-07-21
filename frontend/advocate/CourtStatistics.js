import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {courtsMsg} from '../model';
import {CourtStatistics as StatisticsComponent} from '../components/statistics';
import translate from '../translate';
import courtStatistics from '../courtstatistics';
import {getStatistics} from './selectors';

const mapStateToProps = (state, {court}) => ({
    statistics: getStatistics(state, court),
    courtStatistics: courtStatistics.getStatistics(state, court),
    court: translate.getMessage(state, courtsMsg[court]),
    legend: translate.getMessage(state, 'advocate.statistics.court.legend'),
});

const CourtStatistics = connect(mapStateToProps)(StatisticsComponent);

CourtStatistics.propTypes = {
    court: PropTypes.number.isRequired,
};

export default CourtStatistics;
