import {formatRoute} from '../util';
import {NAME} from './constants';

const getModel = (state) => state.get(NAME);
const getRouteInfo = (state, property, name) => {
    const result = getModel(state).getIn([property, name]);
    return result && result.toJS();
};

export const getCurrentPath = (state, moduleName, route) => formatRoute(
    route,
    getRouteInfo(state, 'params', moduleName),
    getRouteInfo(state, 'query', moduleName),
);
