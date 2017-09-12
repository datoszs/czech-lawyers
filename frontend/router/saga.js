import ga from 'react-ga';
import {take, cancel, fork, call, takeEvery, select, race, put} from 'redux-saga/effects';
import history from '../history';
import {toObject} from '../util';
import {ROUTE_ENTERED, TRANSITION, NAVIGATE, STOP, setRouteMap} from './actions';
import {getHref, getCurrentHref} from './selectors';

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

const transitionListener = function* transition({name, params, query, anchor}) {
    const path = yield select(getHref, name, params, query, anchor);
    if (path) {
        history.push(path);
    } else {
        console.error(`Unknown route name: ${name}`);
    }
};

const navigateSaga = function* navigateSaga({name}) {
    const path = yield select(getCurrentHref, name);
    if (path) {
        history.push(path);
    } else {
        console.error(`Unknown route name: ${name}`);
    }
};

export const routerControlSaga = function* routerControl(modules) {
    const sagaMap = modules.map((module) => [module.NAME, module.saga]).reduce(toObject, {});
    const routeMap = modules.map((module) => [module.NAME, module.ROUTE]).reduce(toObject, {});
    yield put(setRouteMap(routeMap));

    yield takeEvery(TRANSITION, transitionListener);
    yield takeEvery(NAVIGATE, navigateSaga);
    yield call(routerSaga, sagaMap);
};

export default function* routerMainSaga(modules) {
    yield race({
        control: call(routerControlSaga, modules),
        stop: take(STOP),
    });
}
