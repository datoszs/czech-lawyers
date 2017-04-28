import invariant from 'invariant';
import {Record, List} from 'immutable';
import {checkCourt} from './courts';
import {checkResult} from './result';
import {mapDtoToDocument} from './Document';

class CaseDetail extends Record({
    id: 0,
    court: null,
    registry: null,
    advocateId: null,
    advocateName: null,
    result: null,
    documents: [],
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
    result: dto.tagging_result,
    documents: dto.documents.map(mapDtoToDocument),
});

export default CaseDetail;
