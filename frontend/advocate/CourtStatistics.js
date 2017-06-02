import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Statistics, courtsMsg} from '../model';
import {CourtStatistics as StatisticsComponent} from '../components';
import translate from '../translate';
import {getStatistics} from './selectors';

const mapStateToProps = (state, {court}) => {
    const statistics = getStatistics(state, court) || new Statistics();
    return Object.assign({court: translate.getMessage(state, courtsMsg[court])}, statistics.toJS());
};

const CourtStatistics = connect(mapStateToProps)(StatisticsComponent);

CourtStatistics.propTypes = {
    court: PropTypes.number.isRequired,
};

export default CourtStatistics;
