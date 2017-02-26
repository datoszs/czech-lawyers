import {NAME} from './constants';
import {routeEntered} from './actions';
import saga from './saga';

/**
 * ROUTER MODULE
 *
 * Covers interaction with react-router. Ensures that sagas for each route is run when that route is entered.
 */
const router = {
    NAME,
    routeEntered,
    saga,
};
export default router;
