import ga from 'react-ga';
import {take, cancel, fork, call} from 'redux-saga/effects';
import {ROUTE_ENTERED} from './actions';

const setGaPage = (page) => {
    ga.set({page});
    ga.pageview(page);
};

export default function* routerSaga(modules) {
    const sagaMap = modules.reduce((result, module) => Object.assign({[module.NAME]: module.saga}, result), {});

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
}
