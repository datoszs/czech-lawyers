import superagent from 'superagent';
import {CANCEL} from 'redux-saga';

export function RequestError(error) {
    this.status = error.status;
    this.response = error.response.body;
}
RequestError.prototype = Object.create(Error.prototype);
RequestError.prototype.name = 'RequestError';
RequestError.prototype.constructor = RequestError;

export const doGet = (url) => {
    const request = superagent.get(url)
        .accept('json');
    const promise = request.then(
        (response) => response.body,
        (error) => {
            throw new RequestError(error);
        },
    );
    promise[CANCEL] = () => request.abort();
    return promise;
};
