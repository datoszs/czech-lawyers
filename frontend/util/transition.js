import {browserHistory, formatPattern} from 'react-router';
import querystring from 'query-string';

/**
 * Transitions application to a new route (page).
 * @param module Route module (must contain ROUTE constant).
 * @param params Route parameters (optional).
 * @param query Route query (optional).
 */
export default (module, params, query) => {
    let route = formatPattern(module.ROUTE, params);
    if (query) {
        route += `?${querystring.stringify(query)}`;
    }
    browserHistory.push(`/${route}`);
};
