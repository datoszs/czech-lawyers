import {connect} from 'react-redux';
import {BigStatistics} from '../components';
import translate from '../translate';
import {getAdvocate} from './selectors';

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    const msg = {
        msgPositive: translate.getMessage(state, 'stats.positive.legend'),
        msgNegative: translate.getMessage(state, 'stats.negative.legend'),
        msgNeutral: translate.getMessage(state, 'stats.neutral.legend'),
    };
    if (advocate) {
        return Object.assign(advocate.statistics.toJS(), msg);
    } else {
        return Object.assign({
            positive: 0,
            negative: 0,
            neutral: 0,
        }, msg);
    }
};

export default connect(mapStateToProps)(BigStatistics);
