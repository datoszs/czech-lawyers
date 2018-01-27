import invariant from 'invariant';
import moment from 'moment';
import {Record} from 'immutable';
import result, {checkResult} from './result';
import {checkCourt} from './courts';

class Case extends Record({
    id: 0,
    court: null,
    registry: null,
    result: null,
    decisionDate: null,
    propositionDate: null,
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
    result: dto.tagging_result_annuled ? result.ANNULLED : dto.result,
    decisionDate: dto.decision_date && moment(dto.decision_date).valueOf(),
    propositionDate: dto.proposition_date && moment(dto.proposition_date).valueOf(),
});

export default Case;
