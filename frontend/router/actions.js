import {NAME} from './constants';

export const ROUTE_ENTERED = `${NAME}/ROUTE_ENTERED`;
export const TRANSITION = `${NAME}/TRANSITION`;
export const NAVIGATE = `${NAME}/NAVIGATE`;
export const STOP = `${NAME}/STOP`;
export const SET_ROUTE_MAP = `${NAME}/SET_ROUTE_MAP`;

/** Route has been entered. Uses Route onEnter handler */
export const routeEntered = (name, params, query) => ({
    type: ROUTE_ENTERED,
    name,
    params,
    query,
});

export const transition = (name, params, query, anchor) => ({
    type: TRANSITION,
    name,
    params,
    query,
    anchor,
});

export const navigate = (name) => ({
    type: NAVIGATE,
    name,
});

export const stop = () => ({
    type: STOP,
});

export const setRouteMap = (routeMap) => ({
    type: SET_ROUTE_MAP,
    routeMap,
});
