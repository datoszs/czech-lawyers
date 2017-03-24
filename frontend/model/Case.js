import invariant from 'invariant';
import {Record} from 'immutable';
import {checkResult} from './result';
import {checkCourt} from './courts';

class Case extends Record({
    id: 0,
    court: null,
    registry: null,
    result: null,
}) {
    constructor(values) {
        invariant(values.id, 'Case id must be specified');
        super(Object.assign({}, values, {
            result: checkResult(values.result),
            court: checkCourt(values.court),
        }));
    }
}

export const mapDtoToCase = (dto) => ({
    id: dto.id_case,
    court: dto.id_court,
    registry: dto.registry_mark,
    result: dto.result,
});

export default Case;
