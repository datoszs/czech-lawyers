import superagent from 'superagent';
import {CANCEL} from 'redux-saga';
import {call, put} from 'redux-saga/effects';
import unavailable from '../unavailable';
import router from '../router';
import {UNAVAILABLE} from './status';

export function RequestError(error) {
    this.status = error.status;
    this.response = error.response.body;
}
RequestError.prototype = Object.create(Error.prototype);
RequestError.prototype.name = 'RequestError';
RequestError.prototype.constructor = RequestError;

const execute = (request) => {
    const promise = request.then(
        (response) => response.body,
        (error) => {
            throw new RequestError(error);
        },
    );
    promise[CANCEL] = () => request.abort();
    return promise;
};

const wrapExecute = function* executeWrapper(request) {
    try {
        return yield call(execute, request);
    } catch (error) {
        if (error instanceof RequestError && error.status === UNAVAILABLE) {
            yield put(unavailable.enter());
            yield put(router.stop());
        }
        throw error;
    }
};

export const doGet = function* get(url) {
    const request = superagent.get(url)
        .accept('json')
        .set('X-Requested-With', 'XMLHttpRequest');
    return yield* wrapExecute(request);
};

export const doPost = (url) => function* post(body) {
    const request = superagent
        .post(url)
        .send(body)
        .type('form')
        .accept('json')
        .set('X-Requested-With', 'XMLHttpRequest');
    return yield* wrapExecute(request);
};
