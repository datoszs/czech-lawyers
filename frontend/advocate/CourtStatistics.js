import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {courtsMsg} from '../model';
import {CourtStatistics as StatisticsComponent} from '../components/statistics';
import translate from '../translate';
import {getStatistics} from './selectors';

const mapStateToProps = (state, {court}) => {
    const statistics = getStatistics(state, court);
    return {
        court: translate.getMessage(state, courtsMsg[court]),
        positive: (statistics && statistics.positive) || 0,
        negative: (statistics && statistics.negative) || 0,
        neutral: (statistics && statistics.neutral) || 0,
    };
};

const CourtStatistics = connect(mapStateToProps)(StatisticsComponent);

CourtStatistics.propTypes = {
    court: PropTypes.number.isRequired,
};

export default CourtStatistics;
