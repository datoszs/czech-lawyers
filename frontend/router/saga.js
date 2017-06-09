import ga from 'react-ga';
import {take, cancel, fork, call, takeEvery, select} from 'redux-saga/effects';
import history from '../history';
import {toObject, formatRoute} from '../util';
import {ROUTE_ENTERED, TRANSITION, NAVIGATE} from './actions';
import {getQuery, getParams} from './selectors';

const setGaPage = (page) => {
    ga.set({page});
    ga.pageview(page);
};

const routerSaga = function* routerSaga(sagaMap) {
    let currentSaga;
    for (;;) {
        const {name, params, query} = yield take(ROUTE_ENTERED);

        if (currentSaga) {
            yield cancel(currentSaga);
        }

        yield call(setGaPage, name);

        const newSaga = sagaMap[name];
        if (newSaga) {
            currentSaga = yield fork(newSaga, params, query);
        } else {
            currentSaga = null;
        }
    }
};

const transitionListener = (routeMap, {name, params, query, anchor}) => {
    const route = routeMap[name];
    if (route) {
        const path = formatRoute(route, params, query, anchor);
        history.push(path);
    } else {
        console.error(`Unknown route name: ${name}`);
    }
};

const navigateSaga = function* navigateSaga(routeMap, {name}) {
    const route = routeMap[name];
    if (route) {
        const [params, query] = yield [
            select(getParams, name),
            select(getQuery, name),
        ];
        const path = formatRoute(route, params, query);
        history.push(path);
    } else {
        console.error(`Unknown route name: ${name}`);
    }
};

export default function* routerMainSaga(modules) {
    const sagaMap = modules.map((module) => [module.NAME, module.saga]).reduce(toObject, {});
    const routeMap = modules.map((module) => [module.NAME, module.ROUTE]).reduce(toObject, {});

    yield takeEvery(TRANSITION, transitionListener, routeMap);
    yield takeEvery(NAVIGATE, navigateSaga, routeMap);
    yield call(routerSaga, sagaMap);
};
