import {NAME} from './constants';
import {formatRoute} from '../util';

const getModel = (state) => state.get(NAME);
const getRouteInfo = (property) => (state, name) => {
    const result = getModel(state).getIn([property, name]);
    return result && result.toJS();
};

export const getParams = getRouteInfo('params');
export const getQuery = getRouteInfo('query');

export const isActive = (state, module) => getModel(state).get('route') === module;

export const getHref = (state, route, params, query, anchor) =>
    formatRoute(getModel(state).get('routes').get(route), params, query, anchor);
