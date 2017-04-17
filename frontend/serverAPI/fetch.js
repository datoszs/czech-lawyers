import superagent from 'superagent';
import {CANCEL} from 'redux-saga';

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

export const doGet = (url) => {
    const request = superagent.get(url)
        .accept('json');
    return execute(request);
};

export const doPost = (url) => (body) => {
    const request = superagent
        .post(url)
        .send(body)
        .type('form')
        .accept('json');
    return execute(request);
};
