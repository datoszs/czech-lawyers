import {NAME} from './constants';
import reducer from './reducer';
import SuccessContainer from './SuccessContainer';
import ErrorContainer from './ErrorContainer';
import SubmitButton from './SubmitButton';
import saga from './saga';

export default {
    NAME,
    reducer,
    SuccessContainer,
    ErrorContainer,
    saga,
    SubmitButton,
};
