import {NAME} from './constants';
import reducer from './reducer';
import SuccessContainer from './SuccessContainer';
import ErrorContainer from './ErrorContainer';
import saga from './saga';

export default {
    NAME,
    reducer,
    SuccessContainer,
    ErrorContainer,
    saga,
};
