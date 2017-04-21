import {browserHistory} from 'react-router';
import formatRoute from './formatRoute';

/**
 * Transitions application to a new route (page).
 * @param module Route module (must contain ROUTE constant).
 * @param params Route parameters (optional).
 * @param query Route query (optional).
 */
export default (module, params, query) => browserHistory.push(`/${formatRoute(module.ROUTE, params, query)}`);
