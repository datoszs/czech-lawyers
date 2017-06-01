import {put, call} from 'redux-saga/effects';
import {toObject} from '../util';
import {setRoute} from './actions';
import submodules from './submodules';

const sagaMap = submodules.map((submodule) => [submodule.NAME, submodule.saga]).reduce(toObject, {});

export default function* ({request}) {
    yield put(setRoute());
    try {
        const {type, params} = JSON.parse(atob(request));
        const saga = sagaMap[type];
        if (saga) {
            yield put(setRoute(type));
            yield call(saga, params);
        } else {
            console.error(`Unknown link type: ${type}`);
        }
    } catch (ex) {
        console.error(ex.message);
    }
}
