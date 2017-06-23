import {connect} from 'react-redux';
import {BigStatistics} from '../components/statistics';
import translate from '../translate';
import {getAdvocate} from './selectors';

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    return {
        statistics: advocate ? advocate.statistics : undefined,
        msgPositive: translate.getMessage(state, 'stats.positive.legend'),
        msgNegative: translate.getMessage(state, 'stats.negative.legend'),
        msgNeutral: translate.getMessage(state, 'stats.neutral.legend'),
    };
};

export default connect(mapStateToProps)(BigStatistics);
