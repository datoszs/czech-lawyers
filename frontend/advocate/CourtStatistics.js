import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {courtsMsg} from '../model';
import {CourtStatistics as StatisticsComponent} from '../components/statistics';
import translate from '../translate';
import {getStatistics} from './selectors';

const mapStateToProps = (state, {court}) => {
    const statistics = getStatistics(state, court);
    const statisticsProps = statistics ? statistics.toJS() : {positive: 0, negative: 0, neutral: 0};
    return Object.assign({court: translate.getMessage(state, courtsMsg[court])}, statisticsProps);
};

const CourtStatistics = connect(mapStateToProps)(StatisticsComponent);

CourtStatistics.propTypes = {
    court: PropTypes.number.isRequired,
};

export default CourtStatistics;
