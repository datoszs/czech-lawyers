import {connect} from 'react-redux';
import {StatisticsLegend} from '../components/statistics';
import translate from '../translate';

const mapStateToProps = (state) => ({
    positive: translate.getMessage(state, 'stats.positive.legend'),
    negative: translate.getMessage(state, 'stats.negative.legend'),
    neutral: translate.getMessage(state, 'stats.neutral.legend'),
});

export default connect(mapStateToProps)(StatisticsLegend);
