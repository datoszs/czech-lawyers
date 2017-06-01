import {NAME} from './constants';

export const SET_ROUTE = `${NAME}/SET_ROUTE`;

export const setRoute = (type = null) => ({
    type: SET_ROUTE,
    routeType: type,
});
