import {NAME} from './constants';
import {getStatistics, isAvailable} from './selectors';
import reducer from './reducer';
import saga from './saga';

export default {
    NAME,
    isAvailable,
    getStatistics,
    reducer,
    saga,
};
