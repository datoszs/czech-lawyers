import {call, put} from 'redux-saga/effects';
import {courtAPI} from '../serverAPI';
import {setStatistics} from './actions';

export default function* getCourtStatistics() {
    try {
        const statistics = yield call(courtAPI.getStatistics);
        yield put(setStatistics(statistics));
    } catch (ex) {
        console.error(`Unable to retrieve court statistics: ${ex.message}`);
    }
}
