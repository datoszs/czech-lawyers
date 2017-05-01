import {NAME} from './constants';
import reducer from './reducer';
import Container from './Container';
import {initializeValue} from './actions';
import saga from './saga';

export default {
    NAME,
    reducer,
    Container,
    initializeValue,
    saga,
};
