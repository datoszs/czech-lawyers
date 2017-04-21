import {NAME} from './constants';
import {routeEntered} from './actions';
import {getCurrentPath} from './selectors';
import saga from './saga';
import reducer from './reducer';

/**
 * ROUTER MODULE
 *
 * Covers interaction with react-router. Ensures that sagas for each route is run when that route is entered.
 */
const router = {
    NAME,
    routeEntered,
    getCurrentPath,
    saga,
    reducer,
};
export default router;
