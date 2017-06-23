import {NAME} from './constants';
import {getStatistics} from './selectors';
import reducer from './reducer';
import saga from './saga';

export default {
    NAME,
    getStatistics,
    reducer,
    saga,
};
