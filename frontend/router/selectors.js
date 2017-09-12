import {NAME} from './constants';
import {formatRoute} from '../util';

const getModel = (state) => state.get(NAME);
const getRouteInfo = (property) => (state, name) => {
    const result = getModel(state).getIn([property, name]);
    return result && result.toJS();
};

const getParams = getRouteInfo('params');
const getQuery = getRouteInfo('query');

export const isActive = (state, module) => getModel(state).get('route') === module;

export const getHref = (state, route, params, query, anchor) =>
    formatRoute(getModel(state).get('routes').get(route), params, query, anchor);
export const getCurrentHref = (state, route) => getHref(state, route, getParams(state, route), getQuery(state, route));
