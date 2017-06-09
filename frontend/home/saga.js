import {all, call, put} from 'redux-saga/effects';
import {advocateAPI} from '../serverAPI';
import {setLeaderBoard} from './actions';

export default function* () {
    try {
        const [top, bottom] = yield all([
            call(advocateAPI.getTopTen),
            call(advocateAPI.getBottomTen),
        ]);
        yield put(setLeaderBoard(top, bottom));
    } catch (ex) {
        console.error(ex.message);
        yield put(setLeaderBoard());
    }
}
