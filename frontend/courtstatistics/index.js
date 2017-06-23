import {NAME} from './constants';
import {getStatistics, isAvailable} from './selectors';
import reducer from './reducer';
import saga from './saga';

export default {
    NAME,
    getStatistics,
    reducer,
    saga,
};
