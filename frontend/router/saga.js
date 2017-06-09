import ga from 'react-ga';
import {browserHistory} from 'react-router';
import {take, cancel, fork, call, takeEvery} from 'redux-saga/effects';
import {toObject, formatRoute} from '../util';
import {ROUTE_ENTERED, TRANSITION} from './actions';

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

const transitionListener = ({name, params, query, anchor}, routeMap) => {
    const route = routeMap[name];
    if (route) {
        const path = formatRoute(route, params, query, anchor);
        browserHistory.push(path);
    } else {
        console.error(`Unknown route name: ${name}`);
    }
};

export default function* routerMainSaga(modules) {
    const sagaMap = modules.map((module) => [module.NAME, module.saga]).reduce(toObject, {});
    const routeMap = modules.map((module) => [module.NAME, module.ROUTE]).reduce(toObject, {});

    yield takeEvery(TRANSITION, transitionListener, routeMap);
    yield call(routerSaga, sagaMap);
};
