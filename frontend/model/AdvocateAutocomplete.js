import invariant from 'invariant';
import {Record} from 'immutable';

class AdvocateAutocomplete extends Record({
    id: null,
    name: null,
    matched: null,
}) {
    constructor(values) {
        invariant(values.id, 'Advocate id must be specified');
        super(values);
    }
}

export const mapDtoToAdvocateAutocomplete = (dto) => ({
    id: dto.id_advocate,
    name: dto.fullname,
    matched: dto.matched.value,
});

export default AdvocateAutocomplete;
