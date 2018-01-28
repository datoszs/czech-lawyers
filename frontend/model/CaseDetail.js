import invariant from 'invariant';
import {Record, List} from 'immutable';
import moment from 'moment';
import {checkCourt} from './courts';
import result, {checkResult} from './result';
import {mapDtoToDocument} from './Document';

class CaseDetail extends Record({
    id: 0,
    court: null,
    registry: null,
    advocateId: null,
    advocateName: null,
    result: null,
    documents: [],
    advocateFinal: false,
    resultFinal: false,
    decisionDate: null,
    propositionDate: null,
}) {
    constructor(values) {
        invariant(values.id, 'Case id must be specified');
        super(Object.assign({}, values, {
            court: checkCourt(values.court),
            result: checkResult(values.result),
            documents: List(values.documents.map(({id}) => id)),
        }));
    }
}

export const mapDtoToCaseDetail = (dto) => ({
    id: dto.id_case,
    court: dto.id_court,
    registry: dto.registry_mark,
    advocateId: dto.tagging_advocate && dto.tagging_advocate.id_advocate,
    advocateName: dto.tagging_advocate && dto.tagging_advocate.fullname,
    result: dto.tagging_result_annuled ? result.ANNULLED : dto.tagging_result,
    documents: dto.documents.map(mapDtoToDocument),
    advocateFinal: !!dto.tagging_advocate_final,
    resultFinal: !!dto.tagging_result_final,
    propositionDate: dto.proposition_date && moment(dto.proposition_date).valueOf(),
    decisionDate: dto.decision_date && moment(dto.decision_date).valueOf(),
});

export default CaseDetail;
