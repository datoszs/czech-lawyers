import {NAME} from './constants';
import {transition, navigate, stop} from './actions';
import {isActive, getHref, getCurrentHref} from './selectors';
import saga from './saga';
import reducer from './reducer';
import Component from './Component';

/**
 * ROUTER MODULE
 *
 * Covers interaction with react-router. Ensures that sagas for each route is run when that route is entered.
 */
const router = {
    NAME,
    Component,
    transition,
    navigate,
    stop,
    isActive,
    getHref,
    getCurrentHref,
    saga,
    reducer,
};
export default router;
