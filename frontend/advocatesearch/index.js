import {NAME, ROUTE} from './constants';
import {search} from './import';
import Container from './Container';
import saga from './saga';

export default {
    NAME,
    ROUTE,
    Container,
    saga,
    reducer: search.reducer,
};
