import {NAME, ROUTE} from './constants';
import {search} from './modules';
import saga from './saga';
import Container from './Container';

export default {
    NAME,
    ROUTE,
    Container,
    reducer: search.reducer,
    saga,
};
