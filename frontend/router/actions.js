import {NAME} from './constants';

export const ROUTE_ENTERED = `${NAME}/ROUTE_ENTERED`;

/** Route has been entered. Uses Route onEnter handler */
export const routeEntered = (name, params, query) => ({
    type: ROUTE_ENTERED,
    name,
    params,
    query,
});
