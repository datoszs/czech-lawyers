import {browserHistory} from 'react-router';
import formatRoute from './formatRoute';

/**
 * Transitions application to a new route (page).
 * @param route Route path.
 * @param params Route parameters (optional).
 * @param query Route query (optional).
 * @param anchor Anchor (#, optional).
 */
export default (route, params, query, anchor) => browserHistory.push(`/${formatRoute(route, params, query, anchor)}`);
