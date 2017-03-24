import invariant from 'invariant';
import moment from 'moment';
import {Record} from 'immutable';

class Document extends Record({
    id: 0,
    mark: null,
    date: null,
    link: null,
}) {
    constructor(values) {
        invariant(values.id, 'Document id should be specified.');
        super(values);
    }
}

export const mapDtoToDocument = (dto) => ({
    id: dto.id_document,
    mark: dto.mark,
    date: moment(dto.decision_date).valueOf(),
    link: dto.public_link,
});

export default Document;
